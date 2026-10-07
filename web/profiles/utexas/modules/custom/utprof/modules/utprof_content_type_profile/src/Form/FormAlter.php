<?php

namespace Drupal\utprof_content_type_profile\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Supplement form UI to place fields in horizontal tabs.
 */
class FormAlter {

  use StringTranslationTrait;

  const UTPROF_CONTENT_TYPE_PROFILE_CONTACT_INFO_FIELDS = [
    'field_utprof_website_link',
    'field_utprof_phone_number',
    'field_utprof_fax_number',
    'field_utprof_building_code',
    'field_utprof_building_room_numb',
    'field_utprof_contact_form_link',
    'field_utprof_add_contact_info',
  ];

  const UTPROF_CONTENT_TYPE_PROFILE_BASIC_INFO_FIELDS = [
    'field_utprof_add_basic_info',
    'field_utprof_image_ratio_opt',
    'field_utprof_designation',
    'field_utprof_basic_media',
  ];

  const UTPROF_CONTENT_TYPE_PROFILE_STANDARD_FIELDS = [
    'field_utprof_given_name',
    'field_utprof_surname',
  ];

  const UTPROF_CONTENT_TYPE_PROFILE_LISTING_INFO_FIELDS = [
    'field_utprof_profile_groups',
    'field_utprof_profile_tags',
    'field_utprof_listing_link',
  ];

  /**
   * {@inheritdoc}
   */
  public function alterProfileNodeForm(array &$form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'utprof_content_type_profile/node-form';
    $form['#attached']['library'][] = 'utexas_form_elements/link-options';
    $form['tabs'] = [
      '#type' => 'horizontal_tabs',
      '#tree' => TRUE,
      '#weight' => 100,
    ];
    $form['standard_fields'] = [
      '#type' => 'fieldset',
    ];
    $form['email_field'] = [
      '#type' => 'fieldset',
    ];
    $form['eid_field'] = [
      '#type' => 'fieldset',
    ];
    $form['tabs']['display_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Display configuration'),
      '#description' => $this->t('<em>Control the behavior of this content in profile listings.</em>'),
    ];
    $form['tabs']['basic_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Basic Information'),
      '#description' => $this->t('<em>Content in this section displays prominently atop profile pages.</em>'),
    ];
    $form['tabs']['main_content'] = [
      '#type' => 'details',
      '#title' => $this->t('Main Content'),
      '#description' => $this->t('<em>Content in this section can be displayed as a single body field or as multiple horizontal tabs.</em>'),
    ];
    $form['tabs']['contact_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact Information'),
      '#description' => $this->t('<em>Content in this section displays in a sidebar on desktop screens.</em>'),
    ];
    // Rearrange node fields into vertical tabs.
    foreach (self::UTPROF_CONTENT_TYPE_PROFILE_STANDARD_FIELDS as $field) {
      $form['standard_fields'][$field] = $form[$field];
      unset($form[$field]);
    }
    if (isset($form['field_utprof_display_email']) && isset($form['field_utprof_email_address'])) {
      $form['email_field']['field_utprof_display_email'] = $form['field_utprof_display_email'];
      $form['email_field']['field_utprof_display_email']['#weight'] = 1;
      unset($form['field_utprof_display_email']);
      $form['email_field']['field_utprof_email_address'] = $form['field_utprof_email_address'];
      $form['email_field']['field_utprof_email_address']['#weight'] = 0;
      unset($form['field_utprof_email_address']);
    }
    if (isset($form['field_utprof_eid'])) {
      $form['eid_field']['field_utprof_eid'] = $form['field_utprof_eid'];
      unset($form['field_utprof_eid']);
    }
    foreach (self::UTPROF_CONTENT_TYPE_PROFILE_BASIC_INFO_FIELDS as $field) {
      $form['tabs']['basic_information'][$field] = $form[$field];
      unset($form[$field]);
    }
    foreach (self::UTPROF_CONTENT_TYPE_PROFILE_CONTACT_INFO_FIELDS as $field) {
      $form['tabs']['contact_information'][$field] = $form[$field];
      unset($form[$field]);
    }
    $form['tabs']['main_content']['field_utprof_content'] = $form['field_utprof_content'];
    unset($form['field_utprof_content']);
    foreach (self::UTPROF_CONTENT_TYPE_PROFILE_LISTING_INFO_FIELDS as $field) {
      $form['tabs']['display_configuration'][$field] = $form[$field];
      unset($form[$field]);
    }
  }

}
