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

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

// Standard Fast 404 settings copied from default.settings.php.
$config['system.performance']['fast_404']['exclude_paths'] = '/\/(?:styles)|(?:system\/files)\//';
$config['system.performance']['fast_404']['paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)|(xmlrpc.php)|(wp-login.php)$/i';
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
 * Place the config directory outside of the Drupal root.
 */
$settings['config_sync_directory'] = dirname(DRUPAL_ROOT) . '/config';

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
