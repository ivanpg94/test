<?php

declare(strict_types=1);

namespace Drupal\agand\Commands;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\taxonomy\TermInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class AgandCommands extends DrushCommands {

  const TOPIC_ACTIVITY_PATH = '/actividades/por-temas/';
  const TOPIC_PLAN_PATH = '/planes-de-ocio/por-temas/';
  const PROVINCE_ACTIVITY_PATH = '/actividades/por-provincia/';
  const PROVINCE_PLAN_PATH = '/planes-de-ocio/por-provincia/';

  /**
   * Constructs a new AgandCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The alias manager.
   * @param \Drupal\pathauto\AliasCleanerInterface $aliasCleaner
   *   The alias cleaner service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AliasManagerInterface $aliasManager,
    protected AliasCleanerInterface $aliasCleaner
  ) {}

  /**
   * Create missing topic alias for activities.
   *
   * @command agand:alias-topic-activity
   */
  public function aliasTopicActivity(): void {
    $this->askConfirmation();

    $topics = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'topic',
    ]);

    foreach ($topics as $topic) {
      if ($this->aliasExists(static::TOPIC_ACTIVITY_PATH . $topic->id())) {
        continue;
      }

      $this->createAlias(static::TOPIC_ACTIVITY_PATH, $topic);
    }

    $this->io()->writeln('Done.');
  }

  /**
   * Create missing province alias for activities.
   *
   * @command agand:alias-province-activity
   */
  public function aliasProvinceActivity(): void {
    $this->askConfirmation();

    $provinces = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'province',
    ]);

    foreach ($provinces as $province) {
      if ($this->aliasExists(static::PROVINCE_ACTIVITY_PATH . $province->id())) {
        continue;
      }

      $this->createAlias(static::PROVINCE_ACTIVITY_PATH, $province);
    }

    $this->io()->writeln('Done.');
  }

  /**
   * Create missing topic alias for plans.
   *
   * @command agand:alias-topic-plan
   */
  public function aliasTopicPlan(): void {
    $this->askConfirmation();

    $topics = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'topic',
    ]);

    foreach ($topics as $topic) {
      if ($this->aliasExists(static::TOPIC_PLAN_PATH . $topic->id())) {
        continue;
      }

      $this->createAlias(static::TOPIC_PLAN_PATH, $topic);
    }

    $this->io()->writeln('Done.');
  }

  /**
   * Create missing province alias for plans.
   *
   * @command agand:alias-province-plan
   */
  public function aliasProvincePlan(): void {
    $this->askConfirmation();

    $provinces = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'province',
    ]);

    foreach ($provinces as $province) {
      if ($this->aliasExists(static::PROVINCE_PLAN_PATH . $province->id())) {
        continue;
      }

      $this->createAlias(static::PROVINCE_PLAN_PATH, $province);
    }

    $this->io()->writeln('Done.');
  }

  /**
   * Create missing map alias for space types.
   *
   * @command agand:alias-type-space-map
   */
  public function aliasTypeSpaceMap(): void {
    $this->askConfirmation();

    $space_types = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'space',
    ]);

    foreach ($space_types as $space_type) {
      if ($this->aliasExists('/espacios/' . $space_type->id() . '/mapa')) {
        continue;
      }

      // Path is different, not using createAlias().
      $this->io()->text('Creating alias for term ' . $space_type->label());

      $path_alias = $this->entityTypeManager->getStorage('path_alias')->create([
        'path' => '/espacios/' . $space_type->id() . '/mapa',
        'alias' => '/espacios/' . $this->aliasCleaner->cleanString($space_type->label()) . '/mapa',
      ]);
      $path_alias->save();
    }

    $this->io()->writeln('Done.');
  }

  /**
   * Fills the new activity dates field.
   *
   * @command agand:migrate-activity-dates
   */
  public function migrateActivityDates(): void {
    $this->io()->text('Filling new activity dates field.');

    $node_storage = $this->entityTypeManager->getStorage('node');
    $activity_ids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'activity')
      ->condition('field_activity__dates', NULL, 'IS NULL')
      ->condition('field_smart_date', NULL, 'IS NOT NULL')
      ->execute();

    $operations = [];
    foreach ($activity_ids as $activity_id) {
      $operations[] = [
        '\Drupal\agand\Commands\AgandCommands::migrateOneActivityDate', [$activity_id],
      ];
    }

    $batch = [
      'title' => 'Filling new activity dates field',
      'init_message' => 'Starting',
      'error_message' => 'An unrecoverable error has occurred. You can find the error message below. It is advised to copy it to the clipboard for reference.',
      'operations' => $operations,
    ];

    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Sync old activity date field.
   *
   * @command agand:sync-old-activity-date
   */
  public function syncOldActivityDate(): void {
    $this->io()->text('Syncing old activity dates field.');

    $node_storage = $this->entityTypeManager->getStorage('node');
    $activity_ids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'activity')
      ->sort('created', 'desc')
      ->execute();

    $batch_builder = new BatchBuilder();

    foreach ($activity_ids as $activity_id) {
      $batch_builder->addOperation([static::class, 'syncOneActivityOldDate'], [$activity_id]);
    }

    $batch_builder
      ->setTitle('Syncing activity old date field')
      ->setInitMessage('Starting...')
      ->setErrorMessage('An error ocurred.');

    batch_set($batch_builder->toArray());
    drush_backend_batch_process();
  }

  /**
   * Sync the old date field in one activity.
   */
  public static function syncOneActivityOldDate(int $activity_id, \DrushBatchContext $context): void {
    /** @var \Drupal\agand\Entity\Bundle\ActivityInterface $activity */
    $activity = \Drupal::entityTypeManager()->getStorage('node')->load($activity_id);

    if ($activity->field_activity__dates->isEmpty()) {
      return;
    }

    $old_start_date = $activity->field_smart_date->value;
    $old_end_date = $activity->field_smart_date->end_value;

    $new_start_date = $activity->getStartDate()->getTimestamp();
    $new_end_date = $activity->getEndDate()->getTimestamp();

    if ($old_start_date != $new_start_date || $old_end_date != $new_end_date) {
      $activity->set('field_smart_date', [
        'value' => $new_start_date,
        'end_value' => $new_end_date,
      ]);

      $activity->save();
      $context['message'] = 'Updated ' . $activity_id;
    }
  }

  /**
   * Fill the new date field in one activity.
   */
  public static function migrateOneActivityDate(int $activity_id): bool {
    $activity = \Drupal::entityTypeManager()->getStorage('node')->load($activity_id);
    $activity->field_activity__dates = $activity->field_smart_date->getValue();
    $activity->save();

    return TRUE;
  }

  /**
   * Prevent execution without confirmation.
   */
  protected function askConfirmation() {
    $confirm = $this->io()->confirm('Do you want to continue?');
    if (!$confirm) {
      throw new UserAbortException('Command cancelled.');
    }
  }

  /**
   * Checks if an alias already exists for a given path.
   */
  protected function aliasExists(string $unaliased_path): bool {
    $alias = $this->aliasManager->getAliasByPath($unaliased_path);
    return $alias != $unaliased_path;
  }

  /**
   * Creates an alias for a given path.
   */
  protected function createAlias(string $base_path, TermInterface $term): void {
    $this->io()->text('Creating alias for term ' . $term->label());

    $path_alias = $this->entityTypeManager->getStorage('path_alias')->create([
      'path' => $base_path . $term->id(),
      'alias' => $base_path . $this->aliasCleaner->cleanString($term->label()),
    ]);
    $path_alias->save();
  }

}
