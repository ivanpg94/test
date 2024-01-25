<?php

declare(strict_types=1);

namespace Drupal\agand\Entity\Bundle;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A bundle class for space nodes.
 */
class Space extends NodeBundle implements SpaceInterface {

use StringTranslationTrait;    
    /**
   * {@inheritdoc}
   */
  public function getAddressText(?string $bg = NULL): ?array {
    $icon = '';
    $text = '';
    $icon = 'fas fa-location-dot';

    $aux = [];
    if(!empty($this->get('field_address')->getValue()[0]['address_line1'])) {
      $aux[] = $this->get('field_address')->getValue()[0]['address_line1'];
    }
    if(!empty($this->get('field_address')->getValue()[0]['locality'])) {
      $aux[] = $this->get('field_address')->getValue()[0]['locality'];
    }
    if(!empty($this->get('field_address')->getValue()[0]['administrative_area'])) {
      $aux[] = $this->get('field_address')->getValue()[0]['administrative_area'];
    }
    $text = implode(',',$aux);
      
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

}
