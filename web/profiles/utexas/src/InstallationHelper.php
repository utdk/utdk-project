<?php

namespace Drupal\utexas;

use Drupal\Core\File\FileSystemInterface;
use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\file\Entity\File;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Symfony\Component\Yaml\Yaml;

/**
 * Helper methods used during installations & updates.
 */
class InstallationHelper {

  /**
   * Import a default image file for use with metatags.
   *
   * @return object
   *   The file associated with this image.
   */
  public static function uploadDefaultOgImage() {
    /** @var \Drupal\file\FileRepositoryInterface $file_repository */
    $file_repository = \Drupal::service('file.repository');
    $file_system = \Drupal::service('file_system');
    $filedir = 'public://opengraph_images';
    $file_system->prepareDirectory($filedir, FileSystemInterface::CREATE_DIRECTORY);
    $path = \Drupal::service('extension.list.profile')->getPath('utexas') . '/assets/ut_tower.jpg';
    $image = File::create();
    $image->setFileUri($path);
    $image->setOwnerId(\Drupal::currentUser()->id());
    $image->setMimeType(\Drupal::service('file.mime_type.guesser')->guessMimeType($path));
    $image->setFileName(basename($path));
    $destination_dir = 'public://opengraph_images';
    $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY);
    $destination = $destination_dir . '/' . basename($path);
    $file = $file_repository->copy($image, $destination);
    return $file;
  }

  /**
   * Populate default 'Global' metatags.
   */
  public static function populateDefaultMetatags() {
    $defaults = [
      'canonical_url' => '[current-page:url]',
      'og_title' => '[current-page:title]',
      'og_type' => 'website',
      'og_updated_time' => '[node:changed:custom:c]',
      'og_url' => '[current-page:url]',
      'title' => '[current-page:title] | [site:name]',
      'twitter_cards_type' => 'summary',
      'twitter_cards_title' => '[current-page:title]',
    ];
    $metatags = \Drupal::configFactory()
      ->getEditable('metatag.metatag_defaults.global');
    $tags = $metatags->get('tags');
    // Remove deprecated twitter_cards_page_url.
    if (isset($tags['twitter_cards_page_url'])) {
      unset($tags['twitter_cards_page_url']);
    }
    foreach ($defaults as $key => $value) {
      $tags[$key] = $value;
      \Drupal::logger('utexas')->notice("Setting default metatag for $key..");
    }
    // For sites which have not yet set a global OG image, set one.
    if (!isset($tags['og_image'])) {
      \Drupal::logger('utexas')->notice('Setting default global OG image...');
      $og_image = self::uploadDefaultOgImage();
      // Set the file status to 'permanent'.
      \Drupal::service('file.usage')->add($og_image, 'utexas', 'file', $og_image->id());
      \Drupal::state()->set('default_og_image', $og_image->id());
      $uri = $og_image->getFileUri();
      $filepath = \Drupal::service('file_url_generator')->generateString($uri);
      $default_og_image = $filepath;
      $tags['og_image'] = $default_og_image;
    }
    \Drupal::configFactory()
      ->getEditable('metatag.metatag_defaults.global')
      ->set('tags', $tags)->save();
  }

  /**
   * Convert incorrectly migrated metatags robots array to string.
   */
  public static function normalizeRobotsMetatags() {
    $connection = \Drupal::database();
    // Fix both the current node data and all revisions.
    $tables = [
      'node_revision__field_flex_page_metatags',
      'node__field_flex_page_metatags',
    ];
    foreach ($tables as $table) {
      $query = $connection->select($table, 'n');
      $query->fields('n', [
        'entity_id',
        'revision_id',
        'delta',
        'field_flex_page_metatags_value',
      ]);
      $result = $query->execute();
      $results = $result->fetchAll();
      if (!$results || empty($results)) {
        continue;
      }
      foreach ($results as $metatags) {
        // This serialized data is trusted from the component,
        // so we do not restrict object types in unserialize().
        // phpcs:ignore
        $metatags_array = unserialize($metatags->field_flex_page_metatags_value);
        if (!isset($metatags_array['robots'])) {
          // There are no robots declarations. Move on.
          continue;
        }
        if (!is_array($metatags_array['robots'])) {
          // The data is already in the correct string format. Move on.
          continue;
        }
        $new_robots = [];
        // Retrieve any robots declarations that are not 0 and put them in a
        // comma-separated string.
        // Previous format ['nofollow' => 'nofollow', 'noindex' => 'noindex'].
        // New format: "nofollow, noindex".
        foreach ($metatags_array['robots'] as $key => $value) {
          if ($value !== 0) {
            $new_robots[] = $key;
          }
        }
        if (!empty($new_robots)) {
          $metatags_array['robots'] = implode(", ", $new_robots);
        }
        else {
          unset($metatags_array['robots']);
        }
        // Save the new format to the database.
        $new_metatags = serialize($metatags_array);
        $connection->update($table)
          ->fields([
            'field_flex_page_metatags_value' => $new_metatags,
          ])
          ->condition('entity_id', $metatags->entity_id, '=')
          ->condition('revision_id', $metatags->revision_id, '=')
          ->condition('delta', $metatags->delta, '=')
          ->execute();
      }
    }
  }

  /**
   * Populate footer regions with demo content.
   */
  public static function installFooterContent() {
    // Add footer menu links.
    for ($i = 1; $i < 6; $i++) {
      $link = MenuLinkContent::create([
        'title'      => 'Footer Link ' . $i,
        'link'       => ['uri' => 'route:<nolink>'],
        'menu_name'  => 'footer',
        'weight'     => $i,
      ]);
      $link->save();
    }

    // Create block with address placeholder text in 'Footer left' region.
    $block = BlockContent::create([
      'info' => 'Footer Address',
      'type' => 'basic',
      'langcode' => 'en',
      'body' => [
        'value' => '<p>CSU Official Name<br>1234 Street Name St.<br>Austin, Texas, 78712</p><p><a href="tel:555-555-5555">555-555-5555</a><br><em>department@email-here</em></p>',
        'format' => 'flex_html',
      ],
    ]);
    $block->save();
    $config = \Drupal::config('system.theme');
    $placed_block = Block::create([
      'id' => $block->id(),
      'weight' => 0,
      'theme' => $config->get('default'),
      'status' => TRUE,
      'region' => 'footer_left',
      'plugin' => 'block_content:' . $block->uuid(),
      'settings' => [],
    ]);
    $placed_block->save();

    // Create CTA placeholder and place in 'Footer right' region.
    $block = BlockContent::create([
      'info' => 'Footer Call to Action',
      'type' => 'call_to_action',
      'langcode' => 'en',
      'field_utexas_call_to_action_link' => [
        'uri' => 'https://utexas.edu',
        'title' => 'Call to Action',
      ],
    ]);
    $block->save();
    $config = \Drupal::config('system.theme');
    $placed_block = Block::create([
      'id' => $block->id(),
      'weight' => 0,
      'theme' => $config->get('default'),
      'status' => TRUE,
      'region' => 'footer_right',
      'plugin' => 'block_content:' . $block->uuid(),
      'settings' => [],
    ]);
    $placed_block->save();

    // Create placeholder text for 'Footer right' region.
    $block = BlockContent::create([
      'info' => 'Footer Right Placeholder',
      'type' => 'basic',
      'langcode' => 'en',
      'body' => [
        'value' => '<p>This part of the footer may contain any type of content, such as paragraph text, a call-to-action button, a list of links, or a logo or map. Alternatively, leave it blank by removing this placeholder content. See <a href="https://utexas.sharepoint.com/sites/UTDK/SitePages/regions.aspx" target="_blank" class="ut-cta-link--external">documentation</a> about managing content in this region.</p>',
        'format' => 'flex_html',
      ],
    ]);
    $block->save();
    $config = \Drupal::config('system.theme');
    $placed_block = Block::create([
      'id' => $block->id(),
      'weight' => 1,
      'theme' => $config->get('default'),
      'status' => TRUE,
      'region' => 'footer_right',
      'plugin' => 'block_content:' . $block->uuid(),
      'settings' => [],
    ]);
    $placed_block->save();
  }

  /**
   * Populate header regions with demo content.
   */
  public static function installHeaderContent() {
    // Populate header menu links.
    for ($i = 1; $i < 4; $i++) {
      $link = MenuLinkContent::create([
        'title'      => 'Header Link ' . $i,
        'link'       => ['uri' => 'route:<nolink>'],
        'menu_name'  => 'header',
        'weight'     => $i,
      ]);
      $link->save();
    }

    // Populate main menu links.
    $menu_link_titles = [
      'Undergraduate Program' => 'route:<nolink>',
      'Graduate Program' => 'route:<nolink>',
      'Course Directory' => 'route:<nolink>',
      'News' => 'route:<nolink>',
      'Events' => 'route:<nolink>',
      'About' => 'route:<nolink>',
    ];
    $i = 0;
    foreach ($menu_link_titles as $menu_link_title => $uri) {
      $link = MenuLinkContent::create([
        'title'      => $menu_link_title,
        'link'       => ['uri' => $uri],
        'menu_name'  => 'main',
        'weight'     => $i,
        'expanded'   => TRUE,
      ]);
      $link->save();
      $active_link = $link;
      for ($j = 0; $j < 4; $j++) {
        $mid = $active_link->getPluginId();
        $link = MenuLinkContent::create([
          'title'      => 'Lorem Ipsum',
          'link'       => ['uri' => 'route:<nolink>'],
          'menu_name'  => 'main',
          'weight'     => 2,
          'parent'     => $mid,
        ]);
        $link->save();
      }
      $i++;
    }

    // Create block with placeholder text in 'Utility nav' region.
    $block = BlockContent::create([
      'info' => 'Utility Navigation default content',
      'type' => 'basic',
      'langcode' => 'en',
      'body' => [
        'value' => '<p>See <a href="https://utexas.sharepoint.com/sites/UTDK/SitePages/regions.aspx" target="_blank" class="ut-cta-link--external">documentation</a> about managing content in this region</p>',
        'format' => 'flex_html',
      ],
    ]);
    $block->save();
    $config = \Drupal::config('system.theme');
    $placed_block = Block::create([
      'id' => $block->id(),
      'weight' => 0,
      'theme' => $config->get('default'),
      'status' => TRUE,
      'region' => 'utility_nav',
      'plugin' => 'block_content:' . $block->uuid(),
      'settings' => [],
    ]);
    $placed_block->save();
  }

  /**
   * Populate demo social media links.
   */
  public static function installSocialLinks() {
    $block = BlockContent::create([
      'info' => 'Sitewide Social Media Links',
      'type' => 'social_links',
      'langcode' => 'en',
      'field_utexas_sl_social_links' => [
        'icon_size' => 'ut-social-links--small',
        'social_account_links' => serialize([
          [
            'social_account_name' => 'facebook',
            'social_account_url' => 'https://www.facebook.com/UTAustinTX',
          ],
          [
            'social_account_name' => 'x',
            'social_account_url' => 'https://twitter.com/utaustin',
          ],
          [
            'social_account_name' => 'instagram',
            'social_account_url' => 'https://instagram.com/utaustintx',
          ],
          [
            'social_account_name' => 'linkedin',
            'social_account_url' => 'https://www.linkedin.com/edu/the-university-of-texas-at-austin-19518',
          ],
          [
            'social_account_name' => 'youtube',
            'social_account_url' => 'http://www.youtube.com/utaustintexas',
          ],
          [
            'social_account_name' => 'flickr',
            'social_account_url' => 'https://www.flickr.com/photos/utaustin/',
          ],
          [
            'social_account_name' => 'pinterest',
            'social_account_url' => 'https://pinterest.com',
          ],
          [
            'social_account_name' => 'tumblr',
            'social_account_url' => 'http://utaustin.tumblr.com/',
          ],
          [
            'social_account_name' => 'vimeo',
            'social_account_url' => 'https://vimeo.com/utaustin',
          ],
        ]),
      ],
    ]);
    $block->save();

    $config = \Drupal::config('system.theme');
    $footer_block = Block::create([
      'id' => 'footer_sitewide_social_links',
      'weight' => 2,
      'theme' => $config->get('default'),
      'status' => TRUE,
      'region' => 'footer_left',
      'plugin' => 'block_content:' . $block->uuid(),
      'settings' => [],
    ]);
    $footer_block->save();
  }

  /**
   * Set /admin/people configuration to UT Drupal Kit default.
   */
  public static function configurePeopleView() {
    $config_name = 'views.view.user_admin_people';
    $config = \Drupal::configFactory()->getEditable($config_name);
    $config_path = \Drupal::service('extension.list.profile')->getPath('utexas') . '/config/default/' . $config_name . '.yml';
    if (!empty($config_path)) {
      $data = Yaml::parse(file_get_contents($config_path));
      if (is_array($data)) {
        $config->setData($data)->save();
      }
    }
  }

  /**
   * Given a module name, check for active dependencies (config or module).
   *
   * @param string $module
   *   The machine name of the module to check.
   * @param string $check_configuration
   *   Whether to check for configuration dependencies.
   *
   * @return bool
   *   Whether or not the module has an active dependency.
   */
  public static function moduleHasNoActiveDependencies($module, $check_configuration = TRUE) {
    $logger = \Drupal::logger('utexas');
    $t = \Drupal::service('string_translation');
    if (!\Drupal::moduleHandler()->moduleExists($module)) {
      $logger->notice($t->translate('@module is not installed.', ['@module' => $module]));
      return FALSE;
    }
    if (self::moduleHasModuleDependencies($module)) {
      $logger->notice($t->translate('@module has active module dependencies.', ['@module' => $module]));
      return FALSE;
    }
    if ($check_configuration) {
      // Check all configuration for module dependency.
      $config_manager = \Drupal::service('config.manager');
      $dependents = $config_manager->findConfigEntityDependencies('module', [$module]);
      if (!empty($dependents)) {
        $logger->notice($t->translate('@module has active configuration dependencies: @dependents', [
          '@module' => $module,
          '@dependents' => serialize($dependents),
        ]));
        return FALSE;
      }
    }
    $logger->notice($t->translate('@module has no active dependencies and can be uninstalled.', ['@module' => $module]));
    return TRUE;
  }

  /**
   * Given a module name, check if it has active *module* dependencies.
   *
   * @param string $module_to_check
   *   The machine name of the module to check.
   *
   * @return bool
   *   Whether or not the module has an active *module* dependency.
   */
  public static function moduleHasModuleDependencies($module_to_check) {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = \Drupal::moduleHandler();
    $extensions = $module_handler->getModuleList();
    $installed_modules = array_keys($extensions);
    $extension = \Drupal::service("extension.list.module");
    foreach ($installed_modules as $module) {
      if ($module === $module_to_check) {
        continue;
      }
      $active_module_dependencies = [];
      $info = $extension->getExtensionInfo($module);
      $raw_names = isset($info['dependencies']) && is_array($info['dependencies'])
        ? $info['dependencies']
        : [];
      // Normalize dependency strings to module names.
      foreach ($raw_names as $name) {
        // Remove vendor prefixes (e.g., "drupal:ctools" -> "ctools").
        if (strpos($name, ':') !== FALSE) {
          $parts = explode(':', $name, 2);
          $name = $parts[1];
        }
        // Remove trailing version constraints: "module (>=1.2)" -> "module".
        $name = preg_replace('/\s*\(.*\)\s*$/', '', $name);
        // Trim whitespace just in case.
        $name = trim($name);
        $active_module_dependencies[] = $name;
      }
      if (in_array($module_to_check, $active_module_dependencies)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
