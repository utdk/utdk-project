<?php

namespace Drupal\utexas_block_social_links\Services;

/**
 * Class Provide a block of social links.
 *
 * @package UTexasSocialLinkOptions
 */
class UTexasSocialLinkOptions {

  /**
   * Provides a key-value array of social link options.
   *
   * This is used in two places: UTexasSocialLinkField::generateSampleValue()
   * and UTexasSocialLinkWidget->formElement().
   *
   * @return array
   *   A render array.
   */
  public static function getOptionsArray() {
    $social_link_entities = \Drupal::entityTypeManager()->getStorage('utexas_social_links_data')->loadMultiple();
    $social_links_options = [];
    foreach ($social_link_entities as $value) {
      $id = $value->get('id');
      $label = $value->get('label');
      $social_links_options[$id] = $label;
    }
    return $social_links_options;
  }

  /**
   * Provides a key-value array of social link icons.
   *
   * This is used in: UTexasSocialLinkFormatter::viewElements().
   *
   * @return array
   *   A render array.
   */
  public static function getIcons() {
    $social_link_entities = \Drupal::entityTypeManager()->getStorage('utexas_social_links_data')->loadMultiple();
    $social_links_icons = [];
    foreach ($social_link_entities as $value) {
      $id = $value->get('id');
      $icon_path = $value->get('icon');
      $social_links_icons[$id] = $icon_path;
    }
    return $social_links_icons;
  }

}
