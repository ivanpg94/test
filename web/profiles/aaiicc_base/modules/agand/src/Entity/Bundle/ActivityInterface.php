<?php

namespace Drupal\agand\Entity\Bundle;

use Drupal\node\NodeInterface;

/**
 * An interface for activity entities.
 */
interface ActivityInterface extends NodeInterface {

  /**
   * Gets the event start date.
   */
  public function getStartDate(): \DateTimeInterface;

  /**
   * Gets the event end date.
   */
  public function getEndDate(): ?\DateTimeInterface;

  /**
   * Generate text depending on number of spaces or if the activity is online.
   */
  public function getSpacesText(?string $bg = NULL): ?array;

  /**
   * Gets the date string to show on the activity page.
   */
  public function getDatesString(): string;

  /**
   * Checks if the activity is celebrated more than one day.
   */
  public function isMultipleDates(): bool;

}
