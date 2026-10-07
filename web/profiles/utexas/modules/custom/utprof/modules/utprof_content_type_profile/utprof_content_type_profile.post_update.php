<?php

/**
 * @file
 * Post-update functions for UTProf content type.
 */

use Symfony\Component\Yaml\Yaml;

/**
 * Configure XMLSitemap settings.
 */
function utprof_content_type_profile_post_update_configure_xmlsitemap() {
  /** @var \Drupal\Core\Extension\ExtensionPathResolver $extension_path_resolver */
  $extension_path_resolver = \Drupal::service('extension.path.resolver');
  if (\Drupal::moduleHandler()->moduleExists('xmlsitemap') !== FALSE) {
    if (\Drupal::config('xmlsitemap.settings.node.utprof_profile')->get('status') === NULL) {
      $config = \Drupal::configFactory()->getEditable('xmlsitemap.settings.node.utprof_profile');
      $config_path = $extension_path_resolver->getPath('module', 'utprof_content_type_profile') . '/config/install/xmlsitemap.settings.node.utprof_profile.yml';
      if (!empty($config_path)) {
        $data = Yaml::parse(file_get_contents($config_path));
        if (is_array($data)) {
          $config->setData($data)->save();
        }
      }
    }
    else {
      $message = dt('XML Sitemap configuration object "xmlsitemap.settings.node.utprof_profile" found. No action taken.');
      \Drupal::messenger()->addMessage($message);
      \Drupal::logger('utexas')->notice($message);
    }
  }
}
