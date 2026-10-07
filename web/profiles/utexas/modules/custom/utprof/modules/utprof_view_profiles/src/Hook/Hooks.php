<?php

namespace Drupal\utprof_view_profiles\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_plugin_filter_TYPE__CONSUMER_alter().
   */
  #[Hook('plugin_filter_block__layout_builder_alter')]
  public function pluginFilterBlockLayoutBuilderAlter(array &$definitions) {
    // Unset Profile Listing view.
    unset($definitions['views_block:utprof_profiles-block_utprof_profile_listing']);
  }

}
