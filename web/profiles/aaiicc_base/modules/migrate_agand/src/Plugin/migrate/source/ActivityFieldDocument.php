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
 *   id = "d7_activity_field_doc",
 *   source_module = "node"
 * )
 */
class ActivityFieldDocument extends Node {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->join('field_data_field_docuementacion_anexa', 'd', 'n.nid = d.entity_id');
    $query->fields('d', [
      'delta',
    ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';

    $ids['delta']['type'] = 'integer';
    $ids['delta']['alias'] = 'd';

    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $return = parent::prepareRow($row);

    $specific_link = $row->getSourceProperty('field_docuementacion_anexa')[$row->getSourceProperty('delta')];
    $row->setSourceProperty('doc_link', [$specific_link]);

    return $return;
  }

}
