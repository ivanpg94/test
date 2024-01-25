<?php

namespace Drupal\agand\Plugin\Field\FieldFormatter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Drupal\ui_patterns\Form\PatternDisplayFormTrait;
use Drupal\ui_patterns\UiPatternsManager;
use Drupal\ui_patterns\UiPatternsSourceManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'pattern_link' formatter.
 *
 * @FieldFormatter(
 *   id = "pattern_link",
 *   label = @Translation("Link, wrapped in a pattern"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class PatternLinkFormatter extends LinkFormatter {

  use PatternDisplayFormTrait;

  /**
   * UI Patterns manager.
   *
   * @var \Drupal\ui_patterns\UiPatternsManager
   */
  protected $patternsManager;

  /**
   * UI Patterns source manager.
   *
   * @var \Drupal\ui_patterns\UiPatternsSourceManager
   */
  protected $sourceManager;

  /**
   * A module manager object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('path.validator'),
      $container->get('plugin.manager.ui_patterns'),
      $container->get('plugin.manager.ui_patterns_source'),
      $container->get('module_handler')
    );
  }

  /**
   * Constructs a new LinkFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\ui_patterns\UiPatternsManager $patterns_manager
   *   The UI Patterns manager.
   * @param \Drupal\ui_patterns\UiPatternsSourceManager $source_manager
   *   The UI Patterns source manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    PathValidatorInterface $path_validator,
    UiPatternsManager $patterns_manager,
    UiPatternsSourceManager $source_manager,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition, $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings,
      $path_validator
    );

    $this->patternsManager = $patterns_manager;
    $this->sourceManager = $source_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'pattern' => '',
      'variants' => '',
      'pattern_mapping' => [],
      // Used by ui_patterns_settings.
      'pattern_settings' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $field_storage_definition = $this->fieldDefinition->getFieldStorageDefinition();
    $context = [
      'storageDefinition' => $field_storage_definition,
      'limit' => $field_storage_definition->getPropertyNames(),
    ];
    // Some modifications to make 'variant' default value working.
    $configuration = $this->getSettings();
    $pattern = $this->getSetting('pattern');
    $pattern_variant = $this->getCurrentVariant($pattern);
    if (isset($pattern_variant)) {
      $configuration['pattern_variant'] = $pattern_variant;
    }

    $this->buildPatternDisplayForm($form, 'field_wrapper', $context, $configuration);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $pattern = $this->getSetting('pattern');

    if (!empty($pattern)) {
      $pattern_definition = $this->patternsManager->getDefinition($pattern);

      $label = $this->t('None');
      if (!empty($this->getSetting('pattern'))) {
        $label = $pattern_definition->getLabel();
      }
      $summary[] = $this->t('Pattern: @pattern', ['@pattern' => $label]);

      $pattern_variant = $this->getCurrentVariant($pattern);
      if (isset($pattern_variant)) {
        $variant = $this->getSetting('variants')[$pattern_definition->id()];
        $variant = $pattern_definition->getVariant($variant)->getLabel();
        $summary[] = $this->t('Variant: @variant', ['@variant' => $variant]);
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    $pattern = $this->getSetting('pattern');

    // Set pattern fields.
    $fields = [];
    $mapping = $this->getSetting('pattern_mapping');
    $mapping = $mapping[$pattern]['settings'];

    foreach ($mapping as $source => $field) {
      if ($field['destination'] === '_hidden') {
        continue;
      }
      // Get rid of the source tag.
      $source = explode(":", $source)[1];
      if ($source === 'items') {
        $fields[$field['destination']] = $elements;
      }
      if ($source === 'label') {
        $fields[$field['destination']] = $items->getFieldDefinition()->getLabel();
      }
    }

    // Set pattern render array.
    $build = [
      '#type' => 'pattern',
      '#id' => $this->getSetting('pattern'),
      '#fields' => $fields,
    ];

    // Set the variant.
    $pattern_variant = $this->getCurrentVariant($pattern);
    if (isset($pattern_variant)) {
      $build['#variant'] = $pattern_variant;
    }

    // Set the settings.
    $settings = $this->getSetting('pattern_settings');
    $pattern_settings = !empty($settings) && isset($settings[$pattern]) ? $settings[$pattern] : NULL;
    if (isset($pattern_settings)) {
      $build['#settings'] = $pattern_settings;
    }

    // Set pattern context.
    $entity = $items->getEntity();
    $build['#context'] = [
      'type' => 'field_formatter',
      'entity' => $entity,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultValue(array $configuration, $field_name, $value) {
    // Some modifications to make 'destination' default value working.
    $pattern = $configuration['pattern'];
    if (isset($configuration['pattern_mapping'][$pattern]['settings'][$field_name][$value])) {
      return $configuration['pattern_mapping'][$pattern]['settings'][$field_name][$value];
    }
    return NULL;
  }

  /**
   * Checks if a given pattern has a corresponding value on the variants array.
   *
   * @param string $pattern
   *   The pattern.
   *
   * @return string|null
   *   The variant.
   */
  protected function getCurrentVariant($pattern) {
    $variants = $this->getSetting('variants');
    return !empty($variants) && isset($variants[$pattern]) ? $variants[$pattern] : NULL;
  }

}
