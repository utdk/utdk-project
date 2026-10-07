<?php

namespace Drupal\utexas_featured_highlight\Hook;

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
      'utexas_featured_highlight' => [
        'variables' => [
          'headline' => NULL,
          'media_identifier' => NULL,
          'media' => NULL,
          'date' => NULL,
          'copy' => NULL,
          'cta' => NULL,
          'style' => NULL,
        ],
        'template' => 'utexas-featured-highlight',
      ],
    ];
    return $variables;
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_layout_builder_update_block_alter')]
  public function formLayoutBuilderUpdateBlockAlter(&$form, FormStateInterface $form_state, $form_id) {
    $this->simplifyLayoutForm($form);
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_layout_builder_add_block_alter')]
  public function formLayoutBuilderAddBlockAlter(&$form, FormStateInterface $form_state, $form_id) {
    $this->simplifyLayoutForm($form);
  }

  /**
   * Implements hook_form_FORM_ID_alter().
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
          if ($bundle == 'utexas_featured_highlight') {
            $form['settings']['view_mode']['#options'] = $this->updateViewModeLabels($form['settings']['view_mode']['#options']);
          }
        }
      }
    }
  }

  /**
   * Helper function for Layout Builder form alters.
   */
  protected function simplifyLayoutForm(&$form) {
    $is_featured_highlight = FALSE;
    // Check inline block view mode labels.
    if (isset($form['settings']['block_form'])) {
      $bundle = $form['settings']['block_form']['#block']->bundle();
      if ($bundle === 'utexas_featured_highlight') {
        $is_featured_highlight = TRUE;
      }
    }
    // Check reusable block view mode labels.
    if (isset($form['settings']['provider'])) {
      if ($form['settings']['provider']['#value'] == 'block_content') {
        $options = array_keys($form['settings']['view_mode']['#options']);
        if (in_array('utexas_featured_highlight_2', $options)) {
          $is_featured_highlight = TRUE;
        }
      }
    }
    if ($is_featured_highlight) {
      $form['settings']['view_mode']['#options'] = $this->updateViewModeLabels($form['settings']['view_mode']['#options']);
    }
  }

  /**
   * Helper function for updating view mode labels.
   */
  protected function updateViewModeLabels($options) {
    $formatterManager = \Drupal::service('plugin.manager.field.formatter');
    $definitions = $formatterManager->getDefinitions();
    $map = $this->mappingFormatterLabels($definitions, 'utexas_featured_highlight');
    // Use keys from the available $options to get the matching key-value pairs.
    return array_intersect_key($map, $options);
  }

  /**
   * Helper function for generating a mapped array of widget formatter labels.
   */
  protected function mappingFormatterLabels($definitions, $widget_type) {
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
