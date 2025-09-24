<?php

namespace DrupalComposerManaged;

use Composer\Script\Event;

/**
 * Implementation for Composer scripts and Composer hooks.
 */
class ComposerScripts {

  /**
   * Prepare for Composer to update dependencies.
   *
   * Composer will attempt to guess the version to use when evaluating
   * dependencies for path repositories. This has the undesirable effect
   * of producing different results in the composer.lock file depending on
   * which branch was active when the update was executed. This can lead to
   * unnecessary changes, and potentially merge conflicts when working with
   * path repositories on Pantheon multidevs.
   *
   * To work around this problem, it is possible to define an environment
   * variable that contains the version to use whenever Composer would normally
   * "guess" the version from the git repository branch. We set this invariantly
   * to "dev-main" so that the composer.lock file will not change if the same
   * update is later ran on a different branch.
   *
   * @see https://github.com/composer/composer/blob/main/doc/articles/troubleshooting.md#dependencies-on-the-root-package
   */
  public static function preUpdate(Event $event) {
    $io = $event->getIO();

    // We will only set the root version if it has not already been overriden
    if (!getenv('COMPOSER_ROOT_VERSION')) {
      // This is not an error; rather, we are writing to stderr.
      $io->writeError("<info>Using version 'dev-main' for path repositories.</info>");

      putenv('COMPOSER_ROOT_VERSION=dev-main');
    }

    // Apply updates to top-level composer.json
    static::applyComposerJsonUpdates($event);
  }

  /**
   * postUpdate.
   *
   * After "composer update" runs, we have the opportunity to do additional
   * fixups to the project files.
   *
   * @param Composer\Script\Event $event
   *   The Event object passed in from Composer
   */
  public static function postUpdate(Event $event) {
    // for future use
  }

  /**
   * Apply composer.json Updates.
   *
   * During the Composer pre-update hook, check to see if there are any
   * updates that need to be made to the composer.json file. We cannot simply
   * change the composer.json file in the upstream, because doing so would
   * result in many merge conflicts.
   */
  public static function applyComposerJsonUpdates(Event $event) {
    $io = $event->getIO();

    $composerJsonContents = file_get_contents("composer.json");
    $composerJson = json_decode($composerJsonContents, TRUE);
    $originalComposerJson = $composerJson;

    // Check to see if the platform PHP version (which should be major.minor.patch)
    // is the same as the Pantheon PHP version (which is only major.minor).
    // If they do not match, force an update to the platform PHP version. If they
    // have the same major.minor version, then
    $platformPhpVersion = static::getCurrentPlatformPhp($event);
    $pantheonPhpVersion = static::getPantheonPhpVersion($event);
    $updatedPlatformPhpVersion = static::bestPhpPatchVersion($pantheonPhpVersion);
    if (!empty($updatedPlatformPhpVersion) && (empty($platformPhpVersion) || (substr($platformPhpVersion, 0, strlen($pantheonPhpVersion)) != $pantheonPhpVersion))) {
      $io->write("<info>Setting platform.php from '$platformPhpVersion' to '$updatedPlatformPhpVersion' to conform to pantheon php version.</info>");
      $composerJson['config']['platform']['php'] = $updatedPlatformPhpVersion;
    }

    // add our post-update-cmd hook if it's not already present
    $our_hook = 'DrupalComposerManaged\\ComposerScripts::postUpdate';
    // if does not exist, add as an empty arry
    if (!isset($composerJson['scripts']['post-update-cmd'])) {
      $composerJson['scripts']['post-update-cmd'] = [];
    }

    // if exists and is a string, convert to a single-item array (n.b. do not actually need the if exists check because we just assured that it does)
    if (is_string($composerJson['scripts']['post-update-cmd'])) {
      $composerJson['scripts']['post-update-cmd'] = [$composerJson['scripts']['post-update-cmd']];
    }

    // if exists and is an array and does not contain our hook, add our hook (again, only the last check is needed)
    if (!in_array($our_hook, $composerJson['scripts']['post-update-cmd'])) {
      $io->write("<info>Adding post-update-cmd hook to composer.json</info>");
      $composerJson['scripts']['post-update-cmd'][] = $our_hook;

      // enable patching if it isn't already enabled
      if (!isset($composerJson['extra']['enable-patching'])) {
        $io->write("<info>Setting enable-patching to true</info>");
        $composerJson['extra']['enable-patching'] = TRUE;
      }

      $allowed_composer_plugins = [
        'phpstan/extension-installer' => 'Drupal core-dev requires phpstan/extension-installer (https://www.drupal.org/docs/develop/development-tools/phpstan/getting-started)',
        'php-http/discovery' => 'Drupal 10.2 requires php-http/discovery (https://www.drupal.org/project/drupal/issues/3393151)',
        'dealerdirect/phpcodesniffer-composer-installer' => 'Drupal 9.3 requires dealerdirect/phpcodesniffer-composer-installer (https://www.drupal.org/project/drupal/issues/3255749)',
        'tbachert/spi' => 'Drupal core 10.4 requires tbachert/spi (https://www.drupal.org/node/3492353)',
        'drupal/core-recipe-unpack' => 'Drupal core 11.2 requires drupal/core-recipe-unpack (https://www.drupal.org/node/3522189)',
      ];
      foreach ($allowed_composer_plugins as $plugin => $description) {
        if (!isset($composerJson['config']['allow-plugins'][$plugin])) {
          $io->write("<info>$description</info>");
          $composerJson['config']['allow-plugins'][$plugin] = TRUE;
        }
      }
    }

    // Ignore everything in the 'recipes' directory, if it isn't already ignored.
    if (!file_exists('recipes/.gitignore')) {
      $io->write("<info>Adding .gitignore to recipes directory</info>");
      mkdir('recipes');
      file_put_contents('recipes/.gitignore', '*' . PHP_EOL . '!/.gitignore' . PHP_EOL);
    }

    if (serialize($composerJson) == serialize($originalComposerJson)) {
      return;
    }

    // Write the updated composer.json file
    $composerJsonContents = static::jsonEncodePretty($composerJson);
    file_put_contents("composer.json", $composerJsonContents . PHP_EOL);
  }

  /**
   * jsonEncodePretty.
   *
   * Convert a nested array into a pretty-printed json-encoded string.
   *
   * @param array $data
   *   The data array to encode
   *
   * @return string
   *   The pretty-printed encoded string version of the supplied data.
   */
  public static function jsonEncodePretty(array $data) {
    $prettyContents = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $prettyContents = preg_replace('#": \[\s*("[^"]*")\s*\]#m', '": [\1]', $prettyContents);

    return $prettyContents;
  }

  /**
   * Get current platform.php value.
   */
  private static function getCurrentPlatformPhp(Event $event) {
    $composer = $event->getComposer();
    $config = $composer->getConfig();
    $platform = $config->get('platform') ?: [];
    if (isset($platform['php'])) {
      return $platform['php'];
    }
    return NULL;
  }

  /**
   * Get the PHP version from pantheon.yml or pantheon.upstream.yml file.
   */
  private static function getPantheonConfigPhpVersion($path) {
    if (!file_exists($path)) {
      return NULL;
    }
    if (preg_match('/^php_version:\s?(\d+\.\d+)$/m', file_get_contents($path), $matches)) {
      return $matches[1];
    }
  }

  /**
   * Get the PHP version from pantheon.yml.
   */
  private static function getPantheonPhpVersion(Event $event) {
    $composer = $event->getComposer();
    $config = $composer->getConfig();
    $pantheonYmlPath = dirname($config->get('vendor-dir')) . '/pantheon.yml';
    $pantheonUpstreamYmlPath = dirname($config->get('vendor-dir')) . '/pantheon.upstream.yml';

    if ($pantheonYmlVersion = static::getPantheonConfigPhpVersion($pantheonYmlPath)) {
      return $pantheonYmlVersion;
    }
    elseif ($upstreamYmlVersion = static::getPantheonConfigPhpVersion($pantheonUpstreamYmlPath)) {
      return $upstreamYmlVersion;
    }
    return NULL;
  }

  /**
   * Determine which patch version to use when the user changes their platform php version.
   */
  private static function bestPhpPatchVersion($pantheonPhpVersion) {
    // Drupal 11 requires PHP 8.3 at a minimum.
    // Drupal 10 requires PHP 8.1 at a minimum.
    // Drupal 9 requires PHP 7.3 at a minimum.
    // Integrated Composer requires PHP 7.1 at a minimum.
    $patchVersions = [
      '8.3' => '8.3.14',
      '8.2' => '8.2.26',
      '8.1' => '8.1.31',
      // EOL final patch version below this line.
      '8.0' => '8.0.30',
      '7.4' => '7.4.33',
      '7.3' => '7.3.33',
      '7.2' => '7.2.34',
      '7.1' => '7.1.33',
    ];
    if (isset($patchVersions[$pantheonPhpVersion])) {
      return $patchVersions[$pantheonPhpVersion];
    }
    // This feature is disabled if the user selects an unsupported php version.
    return '';
  }

}
