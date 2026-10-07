<?php

namespace Drupal\utexas_photo_content_area\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    $variables = [
      'utexas_photo_content_area' => [
        'variables' => [
          'image' => NULL,
          'photo_credit' => NULL,
          'headline' => NULL,
          'copy' => NULL,
          'links' => [],
        ],
        'template' => 'utexas-photo-content-area',
      ],
    ];
    return $variables;
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_layout_builder_add_block_alter')]
  public function formLayoutBuilderAddBlockAlter(&$form, FormStateInterface $form_state, $form_id) {
    self::simplifyLayoutForm($form);
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_layout_builder_update_block_alter')]
  public function formLayoutBuilderUpdateBlockAlter(&$form, FormStateInterface $form_state, $form_id) {
    self::simplifyLayoutForm($form);
  }

  /**
   * Helper function for Layout Builder form alters.
   */
  protected static function simplifyLayoutForm(&$form) {
    $is_photo_content_area = FALSE;
    // Check inline block view mode labels.
    if (isset($form['settings']['block_form'])) {
      $bundle = $form['settings']['block_form']['#block']->bundle();
      if ($bundle === 'utexas_photo_content_area') {
        $is_photo_content_area = TRUE;
      }
    }
    // Check reusable block view mode labels.
    if (isset($form['settings']['provider'])) {
      if ($form['settings']['provider']['#value'] == 'block_content') {
        $options = array_keys($form['settings']['view_mode']['#options']);
        if (in_array('utexas_photo_content_area_2', $options)) {
          $is_photo_content_area = TRUE;
        }
      }
    }
    if ($is_photo_content_area) {
      $form['settings']['view_mode']['#options'] = self::updateViewModeLabels($form['settings']['view_mode']['#options']);
    }
  }

  /**
   * Helper function for updating view mode labels.
   */
  protected static function updateViewModeLabels($options) {
    $formatterManager = \Drupal::service('plugin.manager.field.formatter');
    $definitions = $formatterManager->getDefinitions();
    $map = self::mapLabels($definitions, 'utexas_photo_content_area');
    // Use keys from the available $options to get the matching key-value pairs.
    return array_intersect_key($map, $options);
  }

  /**
   * Generate a mapped array of the widget formatter labels.
   */
  protected static function mapLabels($definitions, $widget_type) {
    $map = [];
    foreach ($definitions as $key => $value) {
      if (strpos($key, $widget_type) !== FALSE) {
        $map[$key] = $definitions[$key]['label']->__toString();
      }
    }
    // Convert first key to "default" to be used by view modes.
    $map['default'] = $map[$widget_type];
    unset($map[$widget_type]);
    // Sort the array by machine name.
    asort($map);
    return $map;
  }

}
