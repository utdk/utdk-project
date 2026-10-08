<?php

namespace Drupal\utexas_layout_builder_styles\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(&$variables) {
    // Add Layout Builder Styles CSS to all pages.
    $variables['#attached']['library'][] = 'utexas_layout_builder_styles/layout-builder-styles';
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if ($form["#id"] !== 'layout-builder-update-block' && $form["#id"] !== 'layout-builder-add-block') {
      return;
    }

    // Set empty option for utexas_items_per_row group (replacing '- None -').
    if (isset($form['layout_builder_style_utexas_items_per_row']['#empty_option'])) {
      $form['layout_builder_style_utexas_items_per_row']['#empty_option'] = $this->t('No limit (items will adjust to fit the available space)');
    }
  }

}
