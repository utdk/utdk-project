<?php

namespace Drupal\utevent\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public function cron() {
    // Invalidate the events view cache tag if we haven't done so today.
    // This is done so that the events list always shows the proper "start"
    // date of today when it's rendered. If we didn't do this, it's possible
    // that events from previous days could be shown.
    // This relies on using the "Date Sensitive" cache view plugin.
    $state_key = 'events_view_last_cleared';
    $last_cleared = \Drupal::state()->get($state_key);
    $today = date('Y-m-d');
    if ($last_cleared != $today) {
      \Drupal::state()->set($state_key, $today);
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['date_sensitive']);
    }
  }

}
