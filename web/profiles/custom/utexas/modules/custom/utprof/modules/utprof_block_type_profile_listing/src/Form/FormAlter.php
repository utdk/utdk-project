<?php

namespace Drupal\utprof_block_type_profile_listing\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Supplement form UI to place fields in horizontal tabs.
 */
class FormAlter {

  use StringTranslationTrait;

  const UTPROF_BLOCK_TYPE_PROFILE_LISTING_FILTER = [
    'field_utprof_profile_tags',
    'field_utprof_profile_groups',
  ];

  const UTPROF_BLOCK_TYPE_PROFILE_LISTING_PICKER = [
    'field_utprof_specific_profiles',
  ];

  /**
   * {@inheritdoc}
   */
  public function alterProfileListingForm(array &$form, FormStateInterface $form_state) {
    if (!$form['field_utprof_list_method']['widget']['#default_value']) {
      // Set default value for blocks created prior to the functionality in
      // #188.
      $form['field_utprof_list_method']['widget']['#default_value'] = 'filter';
    }
    // Place listing method fields within fieldset wrapper for UX purposes.
    $form['selection'] = [
      '#type' => 'fieldset',
      '#weight' => 3,
    ];
    $form['selection']['field_utprof_list_method'] = $form['field_utprof_list_method'];
    unset($form['field_utprof_list_method']);
    $form['selection']['filter'] = [
      '#type' => 'details',
      '#title' => 'Filters',
      '#weight' => 100,
      '#open' => TRUE,
      '#states' => [
        'invisible' => [
          [':input[name="settings[block_form][field_utprof_list_method]"]' => ['value' => 'pick']],
          'xor',
          [':input[name="field_utprof_list_method"]' => ['value' => 'pick']],
        ],
      ],
    ];

    $form['selection']['picker'] = [
      '#type' => 'fieldset',
      '#weight' => 101,
      '#states' => [
        'invisible' => [
          [':input[name="settings[block_form][field_utprof_list_method]"]' => ['value' => 'filter']],
          'xor',
          [':input[name="field_utprof_list_method"]' => ['value' => 'filter']],
        ],
      ],
    ];
    foreach (self::UTPROF_BLOCK_TYPE_PROFILE_LISTING_FILTER as $field) {
      $form['selection']['filter'][$field] = $form[$field];
      unset($form[$field]);
    }
    foreach (self::UTPROF_BLOCK_TYPE_PROFILE_LISTING_PICKER as $field) {
      $form['selection']['picker'][$field] = $form[$field];
      unset($form[$field]);
    }
  }

}
