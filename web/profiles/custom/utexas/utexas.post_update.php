<?php

/**
 * @file
 * Post update hooks. Applied after all other updates/install functions.
 */

use Symfony\Component\Yaml\Yaml;

/**
 * Update entity_clone cloneable entities configuration.
 */
function utexas_post_update_8150() {
  $config_name = 'entity_clone.cloneable_entities';
  $config = \Drupal::configFactory()->getEditable($config_name);
  $config_path = \Drupal::service('extension.list.profile')->getPath('utexas') . '/config/install/' . $config_name . '.yml';
  if (!empty($config_path)) {
    $data = Yaml::parse(file_get_contents($config_path));
    if (is_array($data)) {
      $config->setData($data)->save();
    }
  }
}

/**
 * Update watermark configuration for Google custom search page.
 */
function utexas_post_update_8151() {
  // Suppress Google watermark on existing sites.
  $config = \Drupal::service('config.factory')->getEditable('search.page.google_cse_search');
  $config->set('configuration.watermark', 0);
  $config->save();

}
