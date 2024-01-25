<?php

namespace Drupal\migrate_agand\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Looks up the value of a property based on a previous migration.
 *
 * @MigrateProcessPlugin(
 *   id = "migrate_lookup_nid_only",
 *   handle_multiples = TRUE
 * )
 */
class MigrateLookupNidOnly extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($this->configuration['migration'])) {
      $this->configuration['migration'] = [$this->configuration['migration']];
    }

    $db = Database::getConnection('default', 'default');
    $results = [];
    foreach ($this->configuration['migration'] as $migration) {
      $query = $db->select('migrate_map_' . $migration, 'mm');
      $query->fields('mm', [
        'destid1',
        'destid2',
      ]);
      $migration_results = $query->condition('sourceid1', $row->getSourceProperty('nid'))
        ->execute()
        ->fetchAll(\PDO::FETCH_ASSOC);

      $results = array_merge($results, $migration_results);
    }

    return $results
      ? $results
      : NULL;
  }

}
