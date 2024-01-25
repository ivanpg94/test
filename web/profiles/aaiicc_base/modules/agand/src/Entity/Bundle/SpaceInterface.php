<?php

namespace Drupal\agand\Entity\Bundle;

use Drupal\node\NodeInterface;

/**
 * An interface for space entities.
 */
interface SpaceInterface extends NodeInterface {

  /**
   * Generate text depending on number of spaces
   */
  public function getAddressText(?string $bg = NULL): ?array;
}
