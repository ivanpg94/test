<?php

namespace Drupal\migrate_agand\Plugin\migrate\source;

use Drupal\file\Plugin\migrate\source\d7\File;

/**
 * Drupal 7 file source from database.
 *
 * Limit files to only those attached to the event image field.
 *
 * @MigrateSource(
 *   id = "custom_d7_file",
 *   source_module = "file"
 * )
 */
class CustomFile extends File {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->join('field_data_field_imagen', 'i', 'f.fid = i.field_imagen_fid');

    return $query;
  }

}
