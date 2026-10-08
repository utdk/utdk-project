<?php

/**
 * @file
 * Contains update functions for the UTexas Flex Content Area module.
 *
 * These functions will only run following ALL hook_update_N() implementations.
 * See hook_post_update_NAME() docs.
 */

/**
 * Issue #1312 : Remove Quick Links view modes and accompanying view displays.
 */
function utexas_flex_content_area_post_update_remove_view_modes(&$sandbox) {
  $entity_type_manager = \Drupal::entityTypeManager();

  // Explicitly delete the entity view display entities before removing the
  // view modes to prevent warnings.
  $entity_view_displays_array = [
    'block_content.utexas_flex_content_area.utexas_flex_content_area_1',
    'block_content.utexas_flex_content_area.utexas_flex_content_area_3',
    'block_content.utexas_flex_content_area.utexas_flex_content_area_4',
  ];
  $entity_view_display_storage = $entity_type_manager->getStorage('entity_view_display');
  $entity_view_displays = $entity_view_display_storage->loadMultiple($entity_view_displays_array);
  $entity_view_display_storage->delete($entity_view_displays);
  // Delete the view modes themselves.
  $entity_view_modes_array = [
    'block_content.utexas_flex_content_area_1',
    'block_content.utexas_flex_content_area_3',
    'block_content.utexas_flex_content_area_4',
  ];
  $entity_view_mode_storage = $entity_type_manager->getStorage('entity_view_mode');
  $entity_view_modes = $entity_view_mode_storage->loadMultiple($entity_view_modes_array);
  $entity_view_mode_storage->delete($entity_view_modes);

  return t('Flex Content Area block view modes and block view displays have been removed.');
}
