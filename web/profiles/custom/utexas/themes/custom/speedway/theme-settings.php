<?php

/**
 * @file
 * Theme settings which allow for configuration settings through the theme UI.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function speedway_form_system_theme_settings_alter(&$form, FormStateInterface $form_state, $form_id = NULL) {
  $theme_settings = \Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider');
  // Speedway custom settings.
  $form['logo']['#weight'] = -1;
  $form['ut_vertical_tabs'] = [
    '#type' => 'vertical_tabs',
  ];
  $setting = $theme_settings->getSetting('logo_height');
  $form['logo']['logo_height'] = [
    '#type' => 'radios',
    '#title' => t('Logo Height'),
    '#description' => t("Most UT Austin logos will work with the 'short' option, but logos that are taller or wider than normal may need to use the 'tall' setting in order to not appear too small. For best results, use an image that is twice as large as the desired display size, for higher pixel density screens."),
    '#options' => [
      'short_logo' => t('Short'),
      'tall_logo' => t('Tall'),
    ],
    '#default_value' => $setting ?? 'short_logo',
  ];
  // Header settings vertical tab - parent entity link settings.
  $form['parent_entity_fieldset'] = [
    '#type' => 'fieldset',
    '#title' => t('Departmental/Organizational Parent Entity (optional)'),
    '#weight' => '0',
  ];
  $form['parent_entity_fieldset']['parent_link_title'] = [
    '#type' => 'textfield',
    '#title' => t('Parent Entity name'),
    '#description' => t("Enter the name of the site's parent college or office. This will appear as a link at the upper left of the header, adjacent to the wordmark."),
    '#default_value' => $theme_settings->getSetting('parent_link_title'),
    '#maxlength' => 256,
  ];
  $form['parent_entity_fieldset']['parent_link'] = [
    '#type' => 'url',
    '#title' => t('Parent Entity website'),
    '#description' => t("Enter the URL to the site's parent college or office."),
    '#default_value' => $theme_settings->getSetting('parent_link'),
    '#maxlength' => 256,
    '#attributes'    => [
      'placeholder' => t('https://'),
    ],
    '#element_validate' => ['_speedway_parent_link_validate'],
  ];
  // Header settings vertical tab - secondary display.
  $form['region_display_fieldset'] = [
    '#type' => 'fieldset',
    '#title' => t('Region display settings'),
    '#weight' => '0',
  ];
  $header_secondary_display_setting = $theme_settings->getSetting('header_secondary_display');
  $form['region_display_fieldset']['header_secondary_display'] = [
    '#type' => 'radios',
    '#title' => t('Header Secondary display'),
    '#description' => t('Display blocks placed in the Header Secondary region as side-by-side or stacked.'),
    '#options' => [
      'side_by_side' => t('Side-by-side'),
      'stacked' => t('Stacked'),
    ],
    '#default_value' => $header_secondary_display_setting ?? 'stacked',
  ];
  // Header settings vertical tab - main menu alignment.
  $form['menu_display_fieldset'] = [
    '#type' => 'fieldset',
    '#title' => t('Main menu settings'),
    '#weight' => '0',
  ];
  $header_main_menu_setting = $theme_settings->getSetting('main_menu_alignment');
  $form['menu_display_fieldset']['main_menu_alignment'] = [
    '#type' => 'radios',
    '#title' => t('Main menu alignment'),
    '#description' => t('Set the alignment of top-level items in the main menu: <ul><li><strong>"Centered, full-width"</strong> (default) optimizes for usability, providing easy access to targets for each menu item</li><li><strong>"Left aligned"</strong> reduces space between targets if there are few menu items, which may look too widely spaced when centered</li><li><strong>"Right aligned"</strong> can provide visual appeal but may negatively affect usability by placing content outside where users expect menu items to appear</li></ul>'),
    '#options' => [
      'full_width_centered' => t('Centered, full-width (recommended)'),
      'left_alignment' => t('Left aligned'),
      'right_alignment' => t('Right aligned'),
    ],
    '#default_value' => $header_main_menu_setting ?? 'full_width_centered',
  ];

  $form['#submit'][] = 'speedway_form_system_theme_settings_submit';
}

/**
 * Helper function to provide validation on Parent Entity Website.
 */
function _speedway_parent_link_validate($element, FormStateInterface &$form_state) {
  $parent_title = $form_state->getValue('parent_link_title');
  $parent_link = $form_state->getValue('parent_link');
  if (!empty($parent_title) && empty($parent_link)) {
    $form_state->setError($element, t('Enter a link for the Parent Entity website. A link is required if you have entered a Parent Entity name.'));
  }
}

/**
 * Theme Settings Submit Callback.
 */
function speedway_form_system_theme_settings_submit($form, FormStateInterface &$form_state) {
  drupal_flush_all_caches();
}
