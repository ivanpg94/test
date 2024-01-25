<?php

declare(strict_types=1);

namespace Drupal\agand\Cron;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\State\StateInterface;
use Drupal\node\NodeStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to apply 'expired' workflow state during cron to ended activities.
 */
class UnpublisherCron implements ContainerInjectionInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The current date as Y-m-d.
   *
   * @var string
   */
  protected $currentDate;

  /**
   * The current date timestamp.
   *
   * @var int
   */
  protected $currentTimestamp;

  /**
   * Class constructor.
   */
  public function __construct(
    NodeStorageInterface $node_storage,
    StateInterface $state,
    LoggerInterface $logger
  ) {
    $this->nodeStorage = $node_storage;
    $this->state = $state;
    $this->logger = $logger;

    $date = new DrupalDateTime();
    $this->currentDate = $date->format('Y-m-d');
    $this->currentTimestamp = $date->getTimestamp();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('state'),
      $container->get('logger.channel.agand')
    );
  }

  /**
   * Applies the 'expired' workflow state to ended activities.
   */
  public function unpublish(): void {
    if (!$this->thresholdExceeded()) {
      return;
    }

    $results = $this->nodeStorage->getQuery()
      ->accessCheck(FALSE)
      ->currentRevision()
      // Can't filter by moderation state directly, using hook_query_TAG_alter.
      ->addTag('moderation_state')
      ->condition('type', ['activity', 'event'], 'IN')
      ->condition('field_smart_date.end_value', $this->currentTimestamp, '<=')
      ->execute();

    foreach ($results as $nid) {
      $node = $this->nodeStorage->load($nid);
      $node->moderation_state = 'expired';
      $node->save();
      $this->logger->info('Node %node set as expired.', ['%node' => $nid]);
    }

    $this->state->set('agand.unpublish_last_execution', $this->currentDate);
  }

  /**
   * Checks if the time threshold has passed.
   */
  protected function thresholdExceeded(): bool {
    return $this->state->get('agand.unpublish_last_execution', '2022-01-01') < $this->currentDate;
  }

}
