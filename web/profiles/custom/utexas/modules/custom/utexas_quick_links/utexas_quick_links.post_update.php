<?php

/**
 * @file
 * Contains update functions for the UTexas Quick Links module.
 *
 * These functions will only run following ALL hook_update_N() implementations.
 * See hook_post_update_NAME() docs.
 */

/**
 * Issue #1303 : Remove Quick Links view modes and accompanying view displays.
 */
function utexas_quick_links_post_update_remove_view_modes(&$sandbox) {
  $entity_type_manager = \Drupal::entityTypeManager();

  // Explicitly delete the entity view display entities before removing the
  // view modes to prevent warnings.
  $entity_view_displays_array = [
    'block_content.utexas_quick_links.utexas_quick_links_2',
    'block_content.utexas_quick_links.utexas_quick_links_3',
    'block_content.utexas_quick_links.utexas_quick_links_4',
  ];
  $entity_view_display_storage = $entity_type_manager->getStorage('entity_view_display');
  $entity_view_displays = $entity_view_display_storage->loadMultiple($entity_view_displays_array);
  $entity_view_display_storage->delete($entity_view_displays);
  // Delete the view modes themselves.
  $entity_view_modes_array = [
    'block_content.utexas_quick_links_2',
    'block_content.utexas_quick_links_3',
    'block_content.utexas_quick_links_4',
  ];
  $entity_view_mode_storage = $entity_type_manager->getStorage('entity_view_mode');
  $entity_view_modes = $entity_view_mode_storage->loadMultiple($entity_view_modes_array);
  $entity_view_mode_storage->delete($entity_view_modes);

  return t('Quick Links block view modes and block view displays have been removed.');
}
