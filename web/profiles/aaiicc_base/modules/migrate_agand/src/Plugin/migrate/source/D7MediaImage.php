<?php

namespace Drupal\migrate_agand\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Media image source plugin.
 *
 * @MigrateSource(
 *   id = "d7_media_image",
 *   source_module = "migrate_agand",
 * )
 */
class D7MediaImage extends SqlBase {

  const FIELDS = [
    'fid',
    'filemime',
    'filename',
  ];

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('file_managed', 'f')
      ->fields('f', static::FIELDS)
      ->condition('filemime', 'image/%', 'LIKE')
      ->orderBy('fid');
    $query->join('field_data_field_imagen', 'i', 'f.fid = i.field_imagen_fid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array_map(fn($field) => [$field => $field], static::FIELDS);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
        'alias' => 'f',
      ],
    ];
  }

}
