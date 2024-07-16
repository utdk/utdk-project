<?php

// @codingStandardsIgnoreFile

/***
 *          ___
 *         /__/\          ___
 *         \  \:\        /  /\
 *          \  \:\      /  /:/
 *      ___  \  \:\    /  /:/
 *     /__/\  \__\:\  /  /::\
 *     \  \:\ /  /:/ /__/:/\:\
 *      \  \:\  /:/  \__\/  \:\
 *       \  \:\/:/        \  \:\
 *        \  \::/          \__\/
 *         \__\/
 *         _____          ___           ___           ___         ___
 *        /  /::\        /  /\         /__/\         /  /\       /  /\
 *       /  /:/\:\      /  /::\        \  \:\       /  /::\     /  /::\
 *      /  /:/  \:\    /  /:/\:\        \  \:\     /  /:/\:\   /  /:/\:\    ___     ___
 *     /__/:/ \__\:|  /  /:/~/:/    ___  \  \:\   /  /:/~/:/  /  /:/~/::\  /__/\   /  /\
 *     \  \:\ /  /:/ /__/:/ /:/___ /__/\  \__\:\ /__/:/ /:/  /__/:/ /:/\:\ \  \:\ /  /:/
 *      \  \:\  /:/  \  \:\/:::::/ \  \:\ /  /:/ \  \:\/:/   \  \:\/:/__\/  \  \:\  /:/
 *       \  \:\/:/    \  \::/~~~~   \  \:\  /:/   \  \::/     \  \::/        \  \:\/:/
 *        \  \::/      \  \:\        \  \:\/:/     \  \:\      \  \:\         \  \::/
 *         \__\/        \  \:\        \  \::/       \  \:\      \  \:\         \__\/
 *                       \__\/         \__\/         \__\/       \__\/
 *          ___
 *         /__/|        ___           ___
 *        |  |:|       /  /\         /  /\
 *        |  |:|      /  /:/        /  /:/
 *      __|  |:|     /__/::\       /  /:/
 *     /__/\_|:|____ \__\/\:\__   /  /::\
 *     \  \:\/:::::/    \  \:\/\ /__/:/\:\
 *      \  \::/~~~~      \__\::/ \__\/  \:\
 *       \  \:\          /__/:/       \  \:\
 *        \  \:\         \__\/         \__\/
 *         \__\/
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! VERY IMPORTANT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! DO NOT MODIFY THIS FILE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * Settings defined in this file are defaults, set by ITS, that should work for most sites.
 * Developers should not directly modify this file, but rather should use context-specific
 * PHP includes to override or supplement these defaults (see the list of pre-defined
 * includes, below).
 */


// BEGIN IP ADDRESS BLOCKING.
if (!function_exists('ip_is_blocked')) {
  // Check the site's private filesystem for the presence of an IP block file.
  $blocked_ip_filepath = __DIR__ . '/files/private/utexas_pantheon_ip_deny_list.txt';
  /**
   * Check if a specified IP address is in a blocklist.
   *
   * @param string $request_remote_addr
   *   An IPv4 value.
   * @param string $blocked_ip_filepath
   *   Path to a valid file location.
   *
   * @return bool
   *   Whether or not the IP address is blocked.
   */
  function ip_is_blocked($request_remote_addr, $blocked_ip_filepath) {
    $request_ip_forbidden = FALSE;
    if (!file_exists($blocked_ip_filepath)) {
      return FALSE;
    }
    if ($file_contents = file($blocked_ip_filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) {
      // Blocked addresses are entered one per line, formatted as
      // IPv4: Single IPs and CIDR.
      // See https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing
      // Convert the list into a PHP array.
      $request_ip_blocklist = $file_contents;
      // Check if this IP is an exact match in the blocklist.
      if (!$request_ip_forbidden = in_array($request_remote_addr, $request_ip_blocklist)) {
        // Check if this IP is in CIDR block list.
        foreach ($request_ip_blocklist as $_cidr) {
          // A CIDR is identified by having the presence of a /.
          // Only evaluate lines that have a / and do not have # or characters.
          if (strpos($_cidr, '/') !== FALSE && strpos($_cidr, '#') === FALSE && strpos($_cidr, ':') === FALSE) {
            $_ip = ip2long($request_remote_addr);
            [$_net, $_mask] = explode('/', $_cidr, 2);
            $_ip_net = ip2long($_net);
            $_ip_mask = ~((1 << (32 - $_mask)) - 1);
            if ($request_ip_forbidden = ($_ip & $_ip_mask) == ($_ip_net & $_ip_mask)) {
              break;
            }
          }
        }
      }
    }
    return $request_ip_forbidden;
  }

  if (ip_is_blocked($_SERVER['REMOTE_ADDR'], $blocked_ip_filepath)) {
    header('HTTP/1.0 403 Forbidden');
    exit;
  }
}
// END IP ADDRESS BLOCKING.

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

// Standard Fast 404 settings copied from default.settings.php.
$config['system.performance']['fast_404']['exclude_paths'] = '/\/(?:styles)|(?:system\/files)\//';
$config['system.performance']['fast_404']['paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)|(xmlrpc.php)|(wp-login.php)|(autodiscover.xml)|(wlmanifest.xml)|(server-status)$/i';
$config['system.performance']['fast_404']['html'] = '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';
$config['system.performance']['fast_404']['enabled'] = TRUE;

$settings['rebuild_access'] = FALSE;

/**
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * If using Pantheon, including its settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all environments that this site
 *      exists in.
 */
$pantheon_settings = __DIR__ . "/settings.pantheon.php";
if (file_exists($pantheon_settings)) {
  include $pantheon_settings;
}

/**
 * Place the config directory outside of the Drupal root.
 */
$settings['config_sync_directory'] = dirname(DRUPAL_ROOT) . '/config';

/**
 * If using Enterprise Login with Pantheon, this file is required.
 *
 * The file itself is provided by `utexas/pantheon_saml_integration`
 */
$pantheon_saml_settings = __DIR__ . "/settings.pantheon.saml.php";
if (file_exists($pantheon_saml_settings)) {
  include $pantheon_saml_settings;
}

/**
 * Individual sites may add a settings.site.php file to include configuration
 * that should travel with the site, instead of placing that configuration
 * directly in settings.php. Typical settings include SMTP integration, trusted
 * host patterns, redirects, and environment-specific overrides.
 *
 * The file itself should be added by the developer and committed to version
 * control. It should not include sensitive information. Those should be
 * referenced externally. See https://pantheon.io/docs/private-paths#private-path-for-files
 */
$site_settings = __DIR__ . "/settings.site.php";
if (file_exists($site_settings)) {
  include $site_settings;
}

/**
 * If there is a local settings file, include it.
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
