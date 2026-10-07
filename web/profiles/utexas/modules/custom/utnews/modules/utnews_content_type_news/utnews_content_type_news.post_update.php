<?php

/**
 * @file
 * Post-update functions for UTNews content type.
 */

use Drupal\utnews_content_type_news\NewsContentTypeHelper;
use Symfony\Component\Yaml\Yaml;

/**
 * Add newly-available social sharing block.
 */
function utnews_content_type_news_post_update_add_social_sharing_block() {
  NewsContentTypeHelper::addSocialSharing();
}

/**
 * Configure XMLSitemap settings.
 */
function utnews_content_type_news_post_update_configure_xmlsitemap() {
  /** @var \Drupal\Core\Extension\ExtensionPathResolver $extension_path_resolver */
  $extension_path_resolver = \Drupal::service('extension.path.resolver');
  if (\Drupal::moduleHandler()->moduleExists('xmlsitemap') !== FALSE) {
    if (\Drupal::config('xmlsitemap.settings.node.utnews_news')->get('status') === NULL) {
      $config = \Drupal::configFactory()->getEditable('xmlsitemap.settings.node.utnews_news');
      $config_path = $extension_path_resolver->getPath('module', 'utnews_content_type_news') . '/config/install/xmlsitemap.settings.node.utnews_news.yml';
      if (!empty($config_path)) {
        $data = Yaml::parse(file_get_contents($config_path));
        if (is_array($data)) {
          $config->setData($data)->save();
        }
      }
    }
    else {
      $message = dt('XML Sitemap configuration object "xmlsitemap.settings.node.utnews_news" found. No action taken.');
      \Drupal::messenger()->addMessage($message);
      \Drupal::logger('utexas')->notice($message);
    }
  }
}
