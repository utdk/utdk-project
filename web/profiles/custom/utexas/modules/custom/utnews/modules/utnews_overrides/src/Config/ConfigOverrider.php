<?php

namespace Drupal\utnews_overrides\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

use Drupal\media\Entity\MediaType;

/**
 * Configuration override.
 */
class ConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];

    $config_name = 'field.field.node.utnews_news.field_utnews_main_media';
    if (in_array($config_name, $names)) {
      $allowed_media_bundles = $this->getImageMediaBundles();
      $overrides[$config_name]['settings']['handler_settings']['target_bundles'] = $allowed_media_bundles;
    }

    $config_name = 'field.field.taxonomy_term.utnews_authors.field_utnews_author_image';
    if (in_array($config_name, $names)) {
      $allowed_media_bundles = $this->getImageMediaBundles();
      $overrides[$config_name]['settings']['handler_settings']['target_bundles'] = $allowed_media_bundles;
    }

    return $overrides;
  }

  /**
   * Provide all available Image Media bundles.
   *
   * @return array
   *   Array of bundles.
   */
  public function getImageMediaBundles() {
    // Get all available media bundles.
    /** @var \Drupal\media\MediaTypeInterface[] $media_bundles */
    $media_bundles = MediaType::loadMultiple();

    // Get only media bundles with a source plugin id of 'image'.
    foreach ($media_bundles as $media_bundle_name => $media_bundle) {
      if ($media_bundle->getSource()->getPluginId() === 'image') {
        $allowed_media_bundles[] = $media_bundle_name;
      }
    }

    return $allowed_media_bundles ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'utnewsConfigOverrider';
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
