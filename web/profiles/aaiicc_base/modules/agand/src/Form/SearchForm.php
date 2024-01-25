<?php

namespace Drupal\agand\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a global search form.
 */
class SearchForm extends FormBase {

  const KEY = 'text';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'agand_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form[static::KEY] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#attributes' => [
        'id' => 'general-search-text',
        'size' => 30,
        'class' => ['border-accent rounded-r-none'],
      ],
      '#label_attributes' => [
        'class' => ['sr-only'],
      ],
    ];

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Search'),
        '#attributes' => [
          'icon' => 'arrow-right',
          'color' => 'secondary',
          'round' => 0,
          'classes' => 'rounded-r-lg',
	  'id' => 'buscar',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect(
      'view.search.page_search_activities',
      [static::KEY => $form_state->getValue(static::KEY)]
    );
  }

}
