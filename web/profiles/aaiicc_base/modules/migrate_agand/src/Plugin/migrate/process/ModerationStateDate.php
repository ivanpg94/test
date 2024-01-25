<?php

namespace Drupal\migrate_agand\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Looks up the value of a property based on a previous migration.
 *
 * @MigrateProcessPlugin(
 *   id = "moderation_state_date",
 * )
 */
class ModerationStateDate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return strtotime('now') >= strtotime($value)
      ? 'expired'
      : 'published';
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

}
