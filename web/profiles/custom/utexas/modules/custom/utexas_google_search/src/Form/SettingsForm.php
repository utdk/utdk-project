<?php

namespace Drupal\utexas_google_search\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\utexas\Form\BaseConfigurationForm;

/**
 * Drupal Kit form to add Google Search.
 */
class SettingsForm extends BaseConfigurationForm {

  /**
   * The actual form elements.
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
    // We allow static calls to services.
    // phpcs:ignore
    $google_pse_id = \Drupal::state()->get('utexas.google_pse_id') ?? '';
    $form['google_pse'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Google Site Search'),
    ];
    $form['google_pse']['google_pse_id'] = [
      '#title' => $this->t('Google Programmable Search Engine ID'),
      '#type' => 'textfield',
      '#default_value' => $google_pse_id,
      '#description' => $this->t('Enter your @google.', [
        '@google' => Link::fromTextAndUrl('Google PSE ID', Url::fromUri('https://programmablesearchengine.google.com/controlpanel/all'))->toString(),
      ]),
      '#required' => TRUE,
    ];
    $form['#submit'][] = [$this, 'submitContentConfig'];
  }

  /**
   * Extended submit callback.
   */
  public function submitContentConfig(&$form, FormStateInterface $form_state) {
    // phpcs:ignore
    $state_api = \Drupal::state();
    $state_api->set('utexas.google_pse_id', $form_state->getValue('google_pse_id'));
  }

}
