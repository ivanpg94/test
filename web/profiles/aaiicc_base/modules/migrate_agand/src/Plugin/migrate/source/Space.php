<?php

namespace Drupal\migrate_agand\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Drupal 7 node source from database.
 *
 * Available configuration keys:
 * - node_type: The node_types to get from the source - can be a string or
 *   an array. If not declared then nodes of all types will be retrieved.
 *
 * Examples:
 *
 * @code
 * source:
 *   plugin: d7_node
 *   node_type: page
 * @endcode
 *
 * In this example nodes of type page are retrieved from the source database.
 *
 * @code
 * source:
 *   plugin: d7_node
 *   node_type: [page, test]
 * @endcode
 *
 * In this example nodes of type page and test are retrieved from the source
 * database.
 *
 * For additional configuration keys, refer to the parent classes.
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 *
 * @MigrateSource(
 *   id = "d7_space",
 *   source_module = "node"
 * )
 */
class Space extends Node {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $return = parent::prepareRow($row);

    $province_id = $row->getSourceProperty('field_provincia');

    if (!empty($province_id[0]['tid'] ?? NULL)) {
      $province_query = $this->select('taxonomy_term_data', 't');
      $province_query->fields('t', ['name']);
      $province = $province_query->condition('tid', $province_id[0]['tid'])
        ->execute()
        ->fetchField();

      $row->setSourceProperty('province', $province);
    }

    $city_id = $row->getSourceProperty('field_localidad');
    if (!empty($city_id[0]['tid'] ?? NULL)) {
      $city_query = $this->select('taxonomy_term_data', 't');
      $city_query->fields('t', ['name']);
      $city = $city_query->condition('tid', $city_id[0]['tid'])
        ->execute()
        ->fetchField();

      $row->setSourceProperty('city', $city);
    }

    return $return;
  }

}
