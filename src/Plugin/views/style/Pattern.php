<?php

namespace Drupal\ui_patterns_views_style\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_patterns\Form\PatternDisplayFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ui_patterns\UiPatternsSourceManager;
use Drupal\ui_patterns\UiPatternsManager;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Style plugin to render items in a pattern field.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "pattern",
 *   title = @Translation("Pattern"),
 *   help = @Translation("Displays content with a pattern."),
 *   theme = "view--pattern",
 *   display_types = {"normal"}
 * )
 */
class Pattern extends StylePluginBase {

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
  protected $usesRowPlugin = TRUE;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\ui_patterns\UiPatternsManager $patterns_manager
   *   UI Patterns manager.
   * @param \Drupal\ui_patterns\UiPatternsSourceManager $source_manager
   *   UI Patterns source manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   A module manager object.
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition, UiPatternsManager $patterns_manager, UiPatternsSourceManager $source_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->patternsManager = $patterns_manager;
    $this->sourceManager = $source_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.ui_patterns'),
      $container->get('plugin.manager.ui_patterns_source'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $context = [];
    $configuration = $this->options;
    $pattern = $this->options['pattern'];
    $pattern_variant = $this->getCurrentVariant($pattern);
    if (isset($pattern_variant)) {
      $configuration['pattern_variant'] = $pattern_variant;
    }
    $this->buildPatternDisplayForm($form, 'view_style', $context, $configuration);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $pattern = $this->options['pattern'];

    // We are using groups ($usesRowPlugin)
    foreach ($build as $delta => $group) {

      $build[$delta]['#options']['pattern'] = $pattern;
      $build[$delta]['#options']['variant'] = $this->getCurrentVariant($pattern);

      // Set pattern fields.
      $fields = [];
      $mapping = $this->options['pattern_mapping'];
      $mapping = $mapping[$pattern]['settings'];
      foreach ($mapping as $source => $field) {
        if ($field['destination'] === '_hidden') {
          continue;
        }
        // Get rid of the source tag.
        $source = explode(":", $source)[1];
        if ($source === 'title') {
          $fields[$field['destination']] = $group['#title'];
        }
        if ($source === 'rows') {
          $fields[$field['destination']] = $group['#rows'];
        }
      }
      $build[$delta]['#options']['fields'] = $fields;
      if (isset($this->options['pattern_settings'][$pattern])) {
        $build[$delta]['#options']['settings'] = $this->options['pattern_settings'][$pattern];
      }
    }
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
   *   A pattern ID.
   *
   * @return string|Null
   *   A pattern variant ID or Null.
   */
  protected function getCurrentVariant($pattern) {
    $variants = $this->options['variants'];
    return !empty($variants) && isset($variants[$pattern]) ? $variants[$pattern] : NULL;
  }

}
