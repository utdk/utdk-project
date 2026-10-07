<?php

namespace Drupal\utnews_view_listing_page\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Configuration override.
 */
class Overrider implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    $title = \Drupal::configFactory()->getEditable('utnews_view_listing_page.config')->get('page_title');
    if ($title) {
      $config_name = 'views.view.utnews_listing_page';
      if (in_array($config_name, $names)) {
        $overrides[$config_name]['display']['default']['display_options']['title'] = $title;
      }
    }
    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'UtNewsViewListingOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
