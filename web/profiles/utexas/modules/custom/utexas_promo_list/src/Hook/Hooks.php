<?php

namespace Drupal\utexas_promo_list\Hook;

use Drupal\block\BlockForm;
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
  public function theme($existing, $type, $theme, $path) {
    $variables = [
      'utexas_promo_list' => [
        'variables' => [
          'headline' => NULL,
          'promo_list_items' => [],
          'wrapper' => NULL,
          'columns' => NULL,
        ],
        'template' => 'utexas-promo-list',
      ],
    ];
    return $variables;
  }

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(&$variables) {
    if ($variables['field_type'] == 'utexas_promo_list' && $variables['element']['#formatter']['type'] == 'utexas_promo_list_3') {
      $variables['attributes']['class'] = 'ut-promo-list-field-wrapper';
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * Add logic to the place block form.
   */
  #[Hook('form_block_form_alter')]
  public function formBlockFormAlter(&$form, &$form_state, $form_id) {
    $bundle = FALSE;
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof BlockForm) {
      /** @var \Drupal\block\Entity\Block $entity */
      $entity = $form_object->getEntity();
      $uuid = $entity->getPlugin()->getDerivativeId();
      /** @var Drupal\block_content\Entity\BlockContent $block_content */
      if (isset($uuid)) {
        $block_content = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $uuid) ?? "";
        if (method_exists($block_content, 'bundle')) {
          $bundle = $block_content->bundle();
          if ($bundle == 'utexas_promo_list') {
            $form['settings']['view_mode']['#options'] = self::updateViewModeLabels($form['settings']['view_mode']['#options']);
          }
        }
      }
    }
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
    $is_promo_list = FALSE;
    // Massage Inline Block View mode labels.
    if (isset($form['settings']['block_form'])) {
      $bundle = $form['settings']['block_form']['#block']->bundle();
      if ($bundle === 'utexas_promo_list') {
        $is_promo_list = TRUE;
      }
    }
    // Massage Reusable Block View mode labels.
    if (isset($form['settings']['provider'])) {
      if ($form['settings']['provider']['#value'] == 'block_content') {
        $options = $form['settings']['view_mode']['#options'];
        if (in_array('utexas_promo_list_3', array_keys($options))) {
          $is_promo_list = TRUE;
        }
      }
    }
    if ($is_promo_list) {
      $form['settings']['view_mode']['#options'] = self::updateViewModeLabels($form['settings']['view_mode']['#options']);
    }
  }

  /**
   * Helper function for updating view mode labels.
   */
  protected static function updateViewModeLabels($options) {
    $formatterManager = \Drupal::service('plugin.manager.field.formatter');
    $definitions = $formatterManager->getDefinitions();
    $map = self::mapLabels($definitions, 'utexas_promo_list');
    // Use keys from the available $options for the matching key-value pairs.
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
    // Sort the arra by machine name.
    asort($map);
    return $map;
  }

}
