<?php

declare(strict_types=1);

namespace Drupal\agand\Entity\Bundle;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A bundle class for activity nodes.
 */
class Activity extends NodeBundle implements ActivityInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getStartDate(): \DateTimeInterface {
    $dates = $this->sortDates();
    $datetime = new \DateTime();

    if (empty($dates)) {
      return $datetime;
    }

    $datetime->setTimestamp((int) current($dates)['value']);
    return $datetime;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate(): ?\DateTimeInterface {
    $dates = $this->sortDates();
    $datetime = new \DateTime();

    if (empty($dates)) {
      return $datetime;
    }

    $datetime->setTimestamp((int) end($dates)['end_value']);
    return $datetime;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatesString(): string {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');
    $start_date = $date_formatter->format($this->getStartDate()->getTimestamp(), 'date_only');
    $end_date = $date_formatter->format($this->getEndDate()->getTimestamp(), 'date_only');

    return $start_date != $end_date
      ? $start_date . ' - ' . $end_date
      : $start_date;
  }

  /**
   * {@inheritdoc}
   */
  public function isMultipleDates(): bool {
    return $this->getStartDate()->format('Y-m-d') !== $this->getEndDate()->format('Y-m-d');
  }

  /**
   * {@inheritdoc}
   */
  public function getSpacesText(?string $bg = NULL): ?array {
    $icon = '';
    $text = '';

    if (!$this->field_on_site->value) {
      $icon = 'fa fa-desktop';
      $text = $this->t('Online activity');
    }
    elseif ($this->field_activity__spaces->count()) {
      $icon = 'fas fa-location-dot';

      /** @var \Drupal\node\NodeInterface|null $space */
      $space = $this->field_activity__spaces->entity;
      $space_link = $space ? $space->toLink(
        $space->field_address->getValue()[0]['address_line1']
        .', '.
        $space->field_address->getValue()[0]['locality']
        .'. '.
        $space->field_address->getValue()[0]['administrative_area']
      ) : NULL;
      $text = $this->field_activity__spaces->count() > 1
        ? $this->t('Multiple spaces')
        : $space_link;
    }
    else {
      return NULL;
    }

    return [
      '#type' => 'pattern',
      '#id' => 'text_with_icon',
      '#variant' => 'inline',
      '#fields' => [
        'text' => $text,
        'icon' => $icon,
      ],
      '#settings' => [
        'accent_color_icon' => !$bg,
        'accent_color_text' => !$bg,
        'fontawesome' => TRUE,
        'bg' => $bg,
        'boxed' => (bool) $bg,
      ],
    ];
  }

  public function getSpacesProvince(?string $bg = NULL): ?array {
    if (!$this->field_on_site->value) {
      return NULL;
    }
    elseif ($this->field_activity__spaces->count()) {
      /** @var \Drupal\node\NodeInterface|null $space */
      $space = $this->field_activity__spaces->entity;
       /** @var \Drupal\taxonomy\TermInterface $prov */
      $prov = $space->field_province->entity;
      if(!empty($prov) && $prov!=NULL) {
	      $url = $prov->toLink()->toRenderable();
	      /*$link = [
	        '#type' => 'link',
	        '#url' => $url,
	        '#title' => $text,
	      ];*/
	      return  $url;
      } else {
	return NULL;
      }
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   *
   * Marks the created activity as imported if the author is external.
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if ($this->isNew() && $this->uid->entity->field_category->value == 'external') {
      $this->set('field_activity__imported', TRUE);
    }

    // Sync old date field (used to sort in lists).
    $this->set('field_smart_date', [
      'value' => $this->getStartDate()->getTimestamp(),
      'end_value' => $this->getEndDate()->getTimestamp(),
    ]);
  }

  /**
   * Sorts the dates chronologically.
   */
  protected function sortDates(): array {
    return smart_date_array_orderby($this->field_activity__dates->getValue(), '#value', SORT_ASC, '#end_value', SORT_ASC);
  }

}
