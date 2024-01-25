<?php

namespace Drupal\migrate_agand\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Sets the time of a datetime to be the last second of the day.
 *
 * @MigrateProcessPlugin(
 *   id = "date_all_day",
 * )
 */
class DateAllDay extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $date = new \DateTime($value);
    $date->modify('+1 day -1 minute');
    return $date->getTimestamp();
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

}
