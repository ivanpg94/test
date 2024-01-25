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
 *   id = "d7_activity",
 *   source_module = "node"
 * )
 */
class Activity extends Node {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('field_data_field_lugar_celebracion', 'l', 'n.nid = l.entity_id');
    $query->fields('l', [
      'field_lugar_celebracion_target_id',
    ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $place_id = $row->getSourceProperty('field_lugar_celebracion_target_id');

    $province_id_query = $this->select('field_data_field_provincia', 'p');
    $province_id_query->fields('p', ['field_provincia_tid']);
    $province_id = $province_id_query->condition('entity_id', $place_id)
      ->execute()
      ->fetchField();

    $row->setSourceProperty('province_id', $province_id);

    $province_query = $this->select('taxonomy_term_data', 't');
    $province_query->fields('t', ['name']);
    $province = $province_query->condition('tid', $province_id)
      ->execute()
      ->fetchField();

    $row->setSourceProperty('province', $province);

    $city_query = $this->select('field_data_field_localidad', 'c');
    $city_query->join('taxonomy_term_data', 't', 't.tid = c.field_localidad_tid');
    $city_query->fields('t', ['name']);
    $city = $city_query->condition('c.entity_id', $place_id)
      ->execute()
      ->fetchField();

    $row->setSourceProperty('city', $city);

    $address_query = $this->select('field_data_field_direccion', 'a');
    $address_query->fields('a', ['field_direccion_value']);
    $address = $address_query->condition('entity_id', $place_id)
      ->execute()
      ->fetchField();

    $row->setSourceProperty('address', $address);

    $postal_code_query = $this->select('field_data_field_codigo_postal', 'pc');
    $postal_code_query->fields('pc', ['field_codigo_postal_value']);
    $postal_code = $postal_code_query->condition('entity_id', $place_id)
      ->execute()
      ->fetchField();

    $row->setSourceProperty('postal_code', $postal_code);

    return parent::prepareRow($row);
  }

}
