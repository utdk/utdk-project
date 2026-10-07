<?php

namespace Drupal\utnews_view_listing_page\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\utnews\Form\BaseConfigurationForm;

/**
 * Supplement form UI to add setting for which blocks & layouts are available.
 */
class ListingPageConfig extends BaseConfigurationForm {

  /**
   * The actual form elements.
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
    $config = $this->configFactory->get('utnews_view_listing_page.config');
    $form['page_title'] = [
      '#title' => $this->t('News listing page title'),
      '#type' => 'textfield',
      '#default_value' => $config->get('page_title'),
      '#description' => $this->t('Displayed on <a href=":link">:link</a>.', [
        ':link' => \Drupal::request()->getSchemeAndHttpHost() . '/news',
      ]),
    ];

    $form['display_thumbnail'] = [
      '#title' => $this->t('Display thumbnail images on news listing page.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('display_thumbnail'),
    ];
    $form['display_date'] = [
      '#title' => $this->t('Display dates on news listing page.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('display_date'),
    ];
    $form['display_summary'] = [
      '#title' => $this->t('Display article summaries on news listing page.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('display_summary'),
    ];

    $form['#submit'][] = [$this, 'submitListingConfig'];
  }

  /**
   * Extended submit callback.
   */
  public function submitListingConfig(&$form, FormStateInterface $form_state) {
    $form_settings = [
      'page_title',
      'display_thumbnail',
      'display_date',
      'display_summary',
    ];
    $config = $this->configFactory->getEditable('utnews_view_listing_page.config');
    foreach ($form_settings as $setting) {
      $value = $form_state->getValue($setting);
      $config->set($setting, $value);
    }
    $config->save();
  }

}
