<?php

namespace Drupal\utexas_instagram_api\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utexas_instagram_api\UTexasInstagramApi;

/**
 * Hook implementations for utexas_instagram_api.
 */
class Hooks {

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public static function cron() {
    // On every cron run, request a new long-lived token using the stored
    // refresh token. These tokens expire every 60 days for security reasons.
    // Cron runs hourly on Pantheon, so there will always be a valid token.
    $accounts = \Drupal::entityTypeManager()->getStorage('utexas_ig_auth')->loadMultiple();
    foreach ($accounts as $account) {
      $instagram_request = new UTexasInstagramApi($account->id());
      $instagram_request->refreshGraphAccessToken();
    }
  }

}
