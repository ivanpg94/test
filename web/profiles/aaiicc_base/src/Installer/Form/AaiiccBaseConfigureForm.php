<?php

namespace Drupal\aaiicc_base\Installer\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AaiiccBaseConfigureForm extends FormBase implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  public function __construct(ModuleExtensionList $moduleInstaller) {
    $this->moduleExtensionList = $moduleInstaller;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.list.module')
    );
  }

  /**
   * @inheritDoc
   */
  public function getFormId(): string {
    return 'aaiicc_base_configure_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['#title'] = $this->t('aaiicc custom modules');

    $form['features'] = [
      '#type' => 'fieldset',
      '#title' => t('Selecciona los modulos que seran instalados'),
      '#description' => t('Features are small modules containing default configuration you can install now or at any point in the future.'),
      '#states' => [
        'visible' => [
          ':input[name="install_demo"]' => ['checked' => FALSE],
        ],
      ],
    ];
// Checkbox para seleccionar todos los módulos
    $form['features']['select_all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Seleccionar todos los módulos'),
      '#attributes' => [
        'class' => ['select-all-modules'],
      ],
      '#ajax' => [
        'callback' => '::selectAllCheckboxCallback',
      ],
    ];
    $modules = $this->getModules();
    foreach ($modules as $module) {
      $form['features'][$module] = [
        '#type' => 'checkbox',
        '#title' => $module,
        '#default_value' => FALSE,
      ];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue installation'),
    ];

    return $form;
  }

  public function selectAllCheckboxCallback(array &$form, FormStateInterface $form_state) {
    // Get the value of the "Select All" checkbox.
    $select_all_value = $form_state->getValue('select_all');

    // Set the values of all other checkboxes based on the "Select All" checkbox.
    $modules = $this->getModules();
    foreach ($modules as $module) {
      $form_state->setValue($module, $select_all_value);
    }

    // Return the entire form.
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $selected_modules = [];
    $modules = $this->getModules();
    foreach ($modules as $module) {
      if ($form_state->getValue($module)) {
        $selected_modules[] = $module;
      }
    }

    $module_installer = \Drupal::service('module_installer');

    foreach ($selected_modules as $module) {
      try {
        $module_installer->install([$module], TRUE);
        if (!$module_installer->install([$module], TRUE)) {
          \Drupal::logger('custom_module')->error('Failed to install module: @module', ['@module' => $module]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('custom_module')->error('Error installing module: @error', ['@error' => $e->getMessage()]);
      }
    }
    // Clear all caches.
    shell_exec('drush cr');
  }
  /**
   * Obtiene los módulos de la ruta de la carpeta `web/modules`.
   *
   * @return array
   *   Una lista de nombres de módulos.
   */
  private function getModules(): array
  {
    $path = \Drupal::root() . '/profiles/aaiicc_base/modules';
    $modules = array_filter(scandir($path), function ($item) {
      return $item[0] !== '.';
    });
    return $modules;
  }
}
