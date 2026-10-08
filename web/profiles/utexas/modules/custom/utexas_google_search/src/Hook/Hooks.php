<?php

namespace Drupal\utexas_google_search\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utexas_google_search\Form\SettingsForm;

/**
 * Hook implementations for Googlesearch.
 */
class Hooks {

  /**
   * Implements hook_form_FORM_ID_alter() for the search_block_form form.
   *
   * Since the exposed form is a GET form, we don't want it to send the form
   * tokens. However, you cannot make this happen in the form builder function
   * itself, because the tokens are added to the form after the builder function
   * is called. So, we have to do it in a form_alter.
   */
  #[Hook('form_utexas_search_form_alter')]
  public function formUtexasSearchFormAlter(&$form, FormStateInterface $form_state): void {
    $form['form_build_id']['#access'] = FALSE;
    $form['form_token']['#access'] = FALSE;
    $form['form_id']['#access'] = FALSE;
  }

  /**
   * Implements hook_form_FORM_ID_alter() for the general config form.
   */
  #[Hook('form_utexas_general_config_alter')]
  public function formUtexasGeneralConfigAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Alter the general config form to include the content configuration.
    \Drupal::classResolver(SettingsForm::class)->alterForm($form, $form_state, $form_id);
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public static function theme($existing, $type, $theme, $path) {
    // Register the Google Search Results theme template.
    return [
      'utexas_google_search_results' => [
        'variables' => [
          'path' => $path,
          'noscript' => NULL,
        ],
        'template' => 'utexas_google_search_results',
      ],
    ];
  }

}
