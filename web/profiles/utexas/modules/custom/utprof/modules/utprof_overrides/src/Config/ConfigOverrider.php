<?php

namespace Drupal\utprof_overrides\Config;

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

    $config_name = 'field.field.node.utprof_profile.field_utprof_basic_media';
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
    // We allow non-dependency injection calls.
    // phpcs:ignore
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
    return 'UtprofConfigOverrider';
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
