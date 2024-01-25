<?php

declare(strict_types=1);

namespace Drupal\agand\Entity\Bundle;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * A base bundle class for all bundle node entities.
 *
 * Contains common functions across all node bundles.
 */
class NodeBundle extends Node {

  /**
   * Gets the URL object for the entity.
   */
  public function getUrl(string $rel = 'canonical', array $options = [], bool $force_current_language = TRUE): Url {
    if ($force_current_language) {
      $current_langcode = $this->languageManager()->getCurrentLanguage()->getId();
      if ($current_langcode != $this->language()->getId() && $this->hasTranslation($current_langcode)) {
        return $this->getTranslation($current_langcode)->toUrl($rel, $options);
      }
    }

    return $this->toUrl($rel, $options);
  }

  /**
   * Gets the URL string for the entity.
   */
  public function getUrlString(
    string $rel = 'canonical',
    array $options = [],
    bool $force_current_language = TRUE
  ): string {
    return $this->getUrl($rel, $options, $force_current_language)->toString();
  }

}
