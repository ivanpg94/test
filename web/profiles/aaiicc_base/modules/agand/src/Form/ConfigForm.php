<?php

namespace Drupal\agand\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure site custom settings.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'agand_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['agand.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('agand.settings');

    $form['smartsupp_active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Smartsupp active'),
      '#default_value' => $config->get('smartsupp_active'),
    ];

    $form['smartsupp_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Smartsupp ID'),
      '#default_value' => $config->get('smartsupp_id'),
      '#description' => $this->t('The ID to integrate Smartsupp Chat.'),
    ];

    $form['main_tickets_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Main tickets url'),
      '#default_value' => $config->get('main_tickets_url'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('agand.settings')
      ->set('smartsupp_active', $form_state->getValue('smartsupp_active'))
      ->set('smartsupp_id', $form_state->getValue('smartsupp_id'))
      ->set('main_tickets_url', $form_state->getValue('main_tickets_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
