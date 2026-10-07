<?php

namespace Drupal\utexas_site_announcement\Services;

/**
 * Provide announcement icon options.
 *
 * @package UTexasAnnouncementIconOptions
 */
class UTexasAnnouncementIconOptions {

  /**
   * Provides a key-value array of announcement icon options.
   *
   * @return array
   *   A render array.
   */
  public static function getLabels() {
    $entities = \Drupal::entityTypeManager()->getStorage('utexas_announcement_icon')->loadMultiple();
    $options = [];
    foreach ($entities as $value) {
      $id = $value->get('id');
      $label = $value->get('label');
      $options[$id] = $label;
    }
    return $options;
  }

  /**
   * Provides a key-value array of announcment icon file paths.
   *
   * @return array
   *   A render array.
   */
  public static function getIcons() {
    $entities = \Drupal::entityTypeManager()->getStorage('utexas_announcement_icon')->loadMultiple();
    $icons = [];
    foreach ($entities as $value) {
      $id = $value->get('id');
      $icon_path = $value->get('icon');
      $icons[$id] = $icon_path;
    }
    return $icons;
  }

}
