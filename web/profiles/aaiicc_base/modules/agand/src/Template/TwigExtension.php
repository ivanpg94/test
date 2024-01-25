<?php

declare(strict_types=1);

namespace Drupal\agand\Template;

use Drupal\agand\Service\HeaderImage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Custom twig functions.
 */
class TwigExtension extends AbstractExtension {

  /**
   * The header image service.
   *
   * @var \Drupal\agand\Service\HeaderImage
   */
  protected $headerImage;

  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new TwigExtension.
   *
   * @param \Drupal\agand\Service\HeaderImage $header_image
   *   The header image service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(HeaderImage $header_image, ConfigFactoryInterface $config_factory) {
    $this->headerImage = $header_image;
    $this->config = $config_factory->get('agand.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('header_image', [$this->headerImage, 'getHeaderImage']),
      new TwigFunction('main_tickets_link', [$this, 'getMainTicketsUrl']),
    ];
  }

  /**
   * Obtains the main tickets url from the module config.
   */
  public function getMainTicketsUrl(): string {
    return $this->config->get('main_tickets_url');
  }

}
