<?php

declare(strict_types=1);

namespace Drupal\agand\Service;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\media\MediaInterface;
use Drupal\path_alias\AliasManager;

/**
 * Service to get the header image for the current page.
 */
class HeaderImage {

  const PATH_PATTERNS = [
    '46d77236-e785-4de3-8b92-46672af3a233' => '/actividades*',
    '85e0832e-c3c0-4d1f-a6af-b64150eca18a' => '/espacios*',
    '51c9127b-73a5-4ff4-abe0-4828e347f577' => '/eventos*',
    '754a78e7-7e85-4d06-9637-6a7530dc309a' => '/planes-de-ocio*,/planes/*',
    '82e1fd1c-011b-41e2-83fb-237774ecaa3c' => '/user*',
    '1367ebd3-93a3-41bb-85e6-dc04b107051b' => '/contacto',
  ];

  const DEFAULT_IMAGE = '10ea977b-7d36-4576-9fdb-3fa28a54f3e1';

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The alias manager service.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $aliasManager;

  /**
   * The path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new HeaderImage.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The entity type manager.
   * @param \Drupal\path_alias\AliasManager $alias_manager
   *   The request stack.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    CurrentPathStack $current_path,
    AliasManager $alias_manager,
    PathMatcherInterface $path_matcher,
    EntityRepositoryInterface $entity_repository,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get the header image for the current page.
   */
  public function getHeaderImage(): ?array {
    $alias = $this->getCurrentAlias();

    foreach (static::PATH_PATTERNS as $media_uuid => $patterns) {
      foreach (explode(',', $patterns) as $pattern) {
        if (!$this->pathMatcher->matchPath($alias, $pattern)) {
          continue;
        }

        return $this->loadImage($media_uuid);
      }
    }

    return $this->loadImage(static::DEFAULT_IMAGE);
  }

  /**
   * Get the current path alias.
   */
  protected function getCurrentAlias(): string {
    return mb_strtolower($this->aliasManager->getAliasByPath($this->currentPath->getPath()));
  }

  /**
   * Loads the media entity by uuid.
   */
  protected function loadImage($uuid): ?array {
    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->entityRepository->loadEntityByUuid('media', $uuid);
    if (!$media instanceof MediaInterface) {
      return NULL;
    }

    return $this->entityTypeManager->getViewBuilder('media')->view($media, 'header_page');
  }

}
