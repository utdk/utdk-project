<?php

/**
 * @file
 * Hooks specific to the utprof Profile listing module.
 */

/**
 * Define which view modes should be selectable in the Profile listing.
 *
 * This hook allows modules to define an array of view modes that should display
 * as selectable options in a Profile listing.
 *
 * @param array $view_mode_options
 *   The view modes that will be displayed, after any processing done by the
 *   utprof_block_type_profile_listing module itself.
 */
function hook_utprof_block_type_profile_listing_alter_view_modes(array &$view_mode_options) {
  // Typical array structure for $view_mode_options.
  // 'node.utexas_basic' => 'Basic'
  // 'node.utexas_name_only' => 'Name Only'
  // 'node.utexas_prominent' => 'Prominent'
  // (Plus any additional view modes defined by the site).
  // Example usage: unset default "Prominent" view mode in favor of customized
  // mysite.prominent view mode (added via the configuration UI).
  unset($view_mode_options['node.utexas_prominent']);
  // Developers may also load all applicable view modes associated with the
  // utprof_profile node using the model found in this module's
  // ViewModeSelection::getAllowedViewModes() method.
}
