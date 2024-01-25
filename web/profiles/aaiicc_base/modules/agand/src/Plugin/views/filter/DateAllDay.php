<?php

namespace Drupal\agand\Plugin\views\filter;

use Drupal\date_popup\DatePopup;

/**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("date_all_day")
 */
class DateAllDay extends DatePopup {

  /**
   * {@inheritdoc}
   */
  protected function opSimple($field) {
    if (!empty($this->value['value'])) {
      $this->value['value'] = $this->value['value'] . ' 23:59:59';
    }

    parent::opSimple($field);
  }

}
