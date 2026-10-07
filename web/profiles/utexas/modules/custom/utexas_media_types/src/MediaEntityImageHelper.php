<?php

namespace Drupal\utexas_media_types;

use Drupal\media\MediaInterface;

/**
 * Helper class to give components multiple media type options.
 */
class MediaEntityImageHelper {

  /**
   * Get the allowed media type bundles.
   *
   * @return array
   *   An array of media types.
   */
  public static function getAllowedBundles() {

    $allowed_bundles = [];
    $media_bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('media');

    /** @var \Drupal\media\MediaTypeInterface $media_type */
    $media_type_manager = \Drupal::entityTypeManager()->getStorage('media_type');

    foreach (array_keys($media_bundles) as $bundle) {
      $source = $media_type_manager->load($bundle)->getSource()->getPluginId();
      if ($source === 'image') {
        $allowed_bundles[] = $bundle;
      }
    }

    return $allowed_bundles;

  }

  /**
   * Get the media file values.
   *
   * @param \Drupal\media\MediaInterface $media
   *   A Drupal media object.
   *
   * @return array
   *   An array of media data.
   */
  public static function getFileFieldValue(MediaInterface $media) {

    $media_bundle = \Drupal::entityTypeManager()->getStorage('media_type')->load($media->bundle());
    $source_field = $media_bundle->getSource()->getSourceFieldDefinition($media_bundle)->getName();

    return $media->get($source_field)->getValue();

  }

  /**
   * Check if the media is restricted.
   *
   * @param mixed $media
   *   A Drupal media object, or 0.
   *
   * @return bool
   *   TRUE if the media item is restricted from the current user.
   */
  public static function mediaIsRestricted($media) {
    if ($media instanceof MediaInterface) {
      $moduleHandler = \Drupal::service('module_handler');
      if ($moduleHandler->moduleExists('utexas_media_access_by_role')) {
        return \Drupal::service('utexas_media_access_by_role.helper')->mediaIsRestricted($media);
      }
    }

    return FALSE;
  }

  /**
   * Check if the media exists.
   *
   * @param int $mid
   *   A Drupal media MID.
   *
   * @return mixed
   *   The MID, if the media still exists. NULL if it does not.
   */
  public static function checkMediaExists($mid) {
    if (!$mid) {
      return NULL;
    }
    $media = \Drupal::entityTypeManager()->getStorage('media')->load($mid);
    if ($media instanceof MediaInterface) {
      return $mid;
    }
    return NULL;
  }

}
