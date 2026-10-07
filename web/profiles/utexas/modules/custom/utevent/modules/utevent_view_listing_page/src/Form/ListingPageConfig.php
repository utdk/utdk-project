<?php

namespace Drupal\utevent_view_listing_page\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\utevent\Form\BaseConfigurationForm;

/**
 * Supplement form UI to add setting for which blocks & layouts are available.
 */
class ListingPageConfig extends BaseConfigurationForm {

  /**
   * The actual form elements.
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
    $config = $this->configFactory->get('utevent_view_listing_page.config');
    $default_title = $config->get('page_title');
    $form['page_title'] = [
      '#title' => $this->t('Event listing page title'),
      '#type' => 'textfield',
      '#default_value' => $default_title ?? 'Events',
      '#description' => $this->t('Displayed on <a href=":link">:link</a>.', [
        ':link' => \Drupal::request()->getSchemeAndHttpHost() . '/events',
      ]),
    ];
    $display_past_events = $config->get('display_past_events');
    $form['display_past_events'] = [
      '#title' => $this->t('Display past events'),
      '#type' => 'checkbox',
      '#default_value' => $display_past_events ?? TRUE,
      '#description' => $this->t('Displayed on <a href=":link">:link</a>.', [
        ':link' => \Drupal::request()->getSchemeAndHttpHost() . '/past-events',
      ]),
    ];
    $display_calendar = $config->get('display_calendar');
    $form['display_calendar'] = [
      '#title' => $this->t('Display calendar (month/week/day)'),
      '#type' => 'checkbox',
      '#default_value' => $display_calendar ?? FALSE,
      '#description' => $this->t('Displayed on <a href=":link">:link</a>.', [
        ':link' => \Drupal::request()->getSchemeAndHttpHost() . '/calendar',
      ]),
    ];

    $form['#submit'][] = [$this, 'submitListingConfig'];
  }

  /**
   * Extended submit callback.
   */
  public function submitListingConfig(&$form, FormStateInterface $form_state) {
    $page_title = $form_state->getValue('page_title');
    $display_past_events = $form_state->getValue('display_past_events');
    $display_calendar = $form_state->getValue('display_calendar');
    $config = $this->configFactory->getEditable('utevent_view_listing_page.config');
    $config->set('page_title', $page_title);
    $config->set('display_past_events', $display_past_events);
    $config->set('display_calendar', $display_calendar);
    $config->save();
    drupal_flush_all_caches();
  }

}
