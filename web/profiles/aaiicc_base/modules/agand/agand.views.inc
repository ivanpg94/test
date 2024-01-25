<?php

/**
 * @file
 * Provides views data for the agand module.
 */

declare(strict_types=1);

use Drupal\field\FieldStorageConfigInterface;

/**
 * Implements hook_field_views_data().
 *
 * Make smart date end value views filter use the whole day.
 */
function agand_field_views_data_alter(array &$data, FieldStorageConfigInterface $field_storage): void {
  $field_name = $field_storage->getName();
  foreach ($data as $table_name => $table_data) {
    if ($field_storage->getType() != 'smartdate') {
      continue;
    }

    $data[$table_name][$field_name . '_end_value']['filter']['id'] = 'date_all_day';
  }
}
