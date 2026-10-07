<?php

namespace Drupal\utexas\Hook;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\block\Entity\Block;
use Drupal\block_content\BlockContentInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\utexas\RenderHelper;
use Drupal\utexas\ThemeHelper;
use Drupal\utexas\ToolbarHandler;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
  * Implements hook_ckeditor5_plugin_info_alter().
  */
  #[Hook('ckeditor5_plugin_info_alter')]
  public function ckeditorPluginInfoAlter(array &$plugin_definitions) {
    if (isset($plugin_definitions['ckeditor5_table'])) {
      $table_plugin_definition = $plugin_definitions['ckeditor5_table']->toArray();
      // Default new CKEditor tables to have 1 header row.
      $table_plugin_definition['ckeditor5']['config']['table']['defaultHeadings'] = [
        'rows' => 1,
        'columns' => 0,
      ];
      $plugin_definitions['ckeditor5_table'] = new CKEditor5PluginDefinition($table_plugin_definition);
    }
  }

  /**
   * Implements hook_contextual_links_plugins_alter().
   */
  #[Hook('contextual_links_plugins_alter')]
  public function contextualLinksPluginAlter(array &$contextual_links) {
    // Change Layout Builder "Configure" link to "Edit" (utdk_profile/2094).
    if (isset($contextual_links['layout_builder_block_update'])) {
      $contextual_links['layout_builder_block_update']['title'] = $this->t('Edit');
    }
    // The three Layout Builder contextual links for blocks are
    // Configure, Move, and Remove Block. To make them all consistent, we drop
    // the 'Block' from the remove block item.
    if (isset($contextual_links['layout_builder_block_remove'])) {
      $contextual_links['layout_builder_block_remove']['title'] = $this->t('Remove');
    }
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Prepopulate Google Tag containers with good defaults.
    if ($form_id === 'google_tag_container_form') {
      /** @var \Drupal\Core\Entity\ContentEntityFormInterface $form_object */
      $form_object = $form_state->getFormObject();
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $entity = $form_object->getEntity();
      if ($entity->isNew()) {
        $form['conditions']['request_path']['pages']['#default_value'] = "/admin*\n/batch*\n/node/add*\n/node/*/edit\n/node/*/delete\n/node/*/layout\n/taxonomy/term/*/edit\n/taxonomy/term/*/layout\n/user/*/edit*\n/user/*/cancel*\n/user/*/layout\n/layout_builder/*";
        $form['conditions']['request_path']['negate']['#default_value'] = TRUE;
        $form['conditions']['response_code']['response_codes']['#default_value'] = "403\n404";
        $form['conditions']['response_code']['negate']['#default_value'] = TRUE;
      }
    }

    if ($form_id === 'utexas_general_config') {
      $utdk_service = \Drupal::state()->get('utexas.utdk_service', '');
      if ($utdk_service === 'utdk-managed') {
        // UTDK Managed sites should not be able to change this setting.
        $form['development_settings']['full_html_updates']['#disabled'] = TRUE;
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_install_configure_form_alter')]
  public function formInstallConfigureFormAlter(&$form, $form_state, $form_id) {
    // Unsetting Country and Timezone selects from installation form.
    unset($form['regional_settings']);
    $user_1_name = 'site-admin';
    // Set default admin account name to site-admin for UI-based installs.
    $form['admin_account']['account']['name']['#default_value'] = $user_1_name;
  }

  /**
   * Implements hook_link_alter().
   */
  #[Hook('link_alter')]
  public function linkAlter(&$variables) {
    // Add CSS 'access-protected' to menu links referencing unpublished nodes.
    $url = $variables['url'];
    if ($url->isRouted()) {
      if ($url->getRouteName() == 'entity.node.canonical') {
        $params = $url->getRouteParameters();
        if ($node = \Drupal::entityTypeManager()->getStorage('node')->load($params['node'])) {
          /** @var \Drupal\node\Entity\Node $node */
          if (!$node->isPublished()) {
            if (isset($variables['options']['attributes']['class']) && !is_array($variables['options']['attributes']['class'])) {
              // Avoid casting to a class as a string, such as in https://git.drupalcode.org/project/redirect/-/blob/8.x-1.x/redirect.module#L375.
              $variables['options']['attributes']['class'] = explode(',', $variables['options']['attributes']['class']);
            }
            // Add the a.access-protected class for CSS styling.
            $variables['options']['attributes']['class'][] = 'access-protected';
            $variables['options']['attributes']['title'] = 'This link is not visible to non-authenticated users.';
          }
        }
      }
    }
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments) {
    /** @var \Drupal\Core\Routing\CurrentRouteMatch $current_route_match */
    $current_route_match = \Drupal::routeMatch();
    $route_name = $current_route_match->getRouteName();
    // Add details fieldset optimizations to all pages.
    $attachments['#attached']['library'][] = 'utexas/menus';
    if (!\Drupal::service('router.admin_context')->isAdminRoute()) {
      $attachments['#attached']['library'][] = 'utexas/auto-anchors';
      // The utexas-provided "Bootstrap" library includes functionality for
      // Bootstrap alert, collapse, tooltips, modals, navs, and tabs.
      // By default, these libraries will be loaded on all non-administrative
      // pages regardless of the active theme. Sites that need to disable these
      // libraries due to conflicts/incompatibility can do so by setting
      // the `utexas_bootstrap_disable` state to `TRUE` (e.g.,
      // `drush state:set utexas_bootstrap_disable TRUE`).
      // See https://www.drupal.org/docs/develop/drupal-apis/state-api/state-api-overview
      if (\Drupal::state()->get('utexas_bootstrap_disable') !== TRUE) {
        $attachments['#attached']['library'][] = 'utexas/bootstrap5-css';
        $layout_builder_routes = [
          'layout_builder.defaults.node.view',
          'layout_builder.overrides.node.view',
        ];
        if (!in_array($route_name, $layout_builder_routes)) {
          $attachments['#attached']['library'][] = 'utexas/bootstrap5-js';
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_addtoany_standard().
   */
  #[Hook('preprocess_addtoany_standard')]
  public function preprocessAddToAny(&$variables) {
    $variables['link_title'] = strip_tags($variables['link_title']);
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    $base_plugin_id = $variables['base_plugin_id'];
    $content = $variables['elements']['content'] ?? [];
    if (in_array($base_plugin_id, ['menu_block', 'system_menu_block'])) {
      if (isset($variables['elements']['#id'])) {
        $variables['content']['#attributes']['menu-block-id'] = $variables['elements']['#id'];
      }
    }
    if (isset($content['#block_content']) && $content['#block_content'] instanceof BlockContentInterface) {
      // Add bundle identifier.
      $variables['attributes']['class'][] = Html::cleanCssIdentifier('block-bundle-' . $content['#block_content']->bundle());
      // Add legacy identifier to our implementation of feed_block module.
      if ($content['#block_content']->bundle() === 'feed_block') {
        $variables['attributes']['class'][] = 'ut-newsreel';
      }
    }

    if (in_array($base_plugin_id, ['menu_block', 'addtoany_block', 'addtoany_follow_block'])) {
      // AddToAny and Menu block titles should use the smaller `ut-headline`.
      $variables['title_attributes']['class'][] = 'ut-headline';
    }
    else {
      // All other block titles should use `ut-headline--xl`.
      $variables['title_attributes']['class'][] = 'ut-headline--xl';
    }
  }

  /**
   * Implements hook_preprocess__block_system_messages_block().
   */
  #[Hook('preprocess_block__system_messages_block')]
  public function preprocessBlockSytemMessagesBlock(&$variables) {
    $variables['content']['#include_fallback'] = FALSE;
  }

  /**
   * Implements hook_preprocess_breadcrumb().
   */
  #[Hook('preprocess_breadcrumb')]
  public function preprocessBreadcrumb(&$variables) {
    // Use a placeholder to inject dynamic content.
    $placeholder_title = [
      '#lazy_builder' => [
        RenderHelper::class . '::lazyBuilder',
        ['page_title'],
      ],
      '#create_placeholder' => TRUE,
    ];
    $variables['breadcrumb'][] = [
      'text' => $placeholder_title,
    ];
  }

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(&$variables, $hook) {
    if (!isset($variables['element']['#bundle'])) {
      return;
    }
    switch ($variables['element']['#bundle']) {
      case 'feed_block':
        if ($variables['element']['#field_name'] === 'field_read_more') {
          // Add 'button' class to Read more <a> tag.
          $variables['attributes']['class'][] = 'ut-cta';
          $variables['items'][0]['content']['#options']['attributes']['class'][] = 'ut-btn--secondary';
        }
        if ($variables['element']['#field_name'] === 'field_intro_text') {
          $variables['attributes']['class'][] = 'ut-copy';
        }
        break;

      case 'basic':
        if ($variables['element']['#field_name'] === 'body' && $variables['element']['#entity_type'] === 'block_content') {
          $variables['attributes']['class'][] = 'ut-copy';
        }
        break;
    }
  }

  /**
   * Implements hook_preprocess_html().
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(&$variables) {
    $variables['page']['#attached']['html_head'][] = [
      [
        '#tag' => 'meta',
        '#attributes' => [
          'name' => 'utexas-utdk-version',
          'content' => '3',
        ],
      ],
      'utexas-utdk-version',
    ];
    if (!empty($variables['node_type'])) {
      $node_type = $variables['node_type'];
      $variables['attributes']['class'][] = 'page__' . $node_type;
    }
  }

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(&$variables) {
    // If the current page uses Layout Builder, add a flag.
    if (ThemeHelper::isLayoutBuilderPage()) {
      $variables['is_layout_builder_page'] = TRUE;
    }
    // Year for use in footer copyright.
    $variables['year'] = date('Y');
    /** @var \Drupal\Core\Routing\CurrentRouteMatch $current_route_match */
    $current_route_match = \Drupal::routeMatch();
    $route_name = $current_route_match->getRouteName();
    if ($route_name === 'utexas_google_search.search') {
      // Omit the entire breadcrumb region from the page.
      unset($variables['page']['breadcrumb']);
    }
  }

  /**
   * Implements hook_preprocess_status_messages().
   */
  #[Hook('preprocess_status_messages')]
  public function preprocessStatusMessages(&$variables) {
    $variables['#attached']['library'][] = 'utexas/status-messages';
    $variables['attributes']['class'][] = 'status-messages';
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    // Register templates defined in /templates.
    return [
      'block__addtoany' => [
        'base hook' => 'block',
      ],
    ];
  }

  /**
   * Implements hook_themes_installed().
   */
  #[Hook('themes_installed')]
  public function themesInstalled($theme_list) {
    // The theme 'Speedway' is being installed.
    if (in_array('speedway', $theme_list)) {

      $config = \Drupal::config('system.theme');
      $default_theme = $config->get('default');
      $theme_config = \Drupal::config($default_theme . '.settings');

      // Load additional theme settings.
      $link = $theme_config->get('parent_link');
      $title = $theme_config->get('parent_link_title');
      $logo_height = $theme_config->get('logo_height');
      $header_secondary_display = $theme_config->get('header_secondary_display');
      $main_menu_alignment = $theme_config->get('main_menu_alignment');

      // Map theme settings to Speedway.
      \Drupal::logger('utexas')->notice('Mapping your theme settings to Speedway...');
      $speedway = \Drupal::configFactory()->getEditable('speedway.settings');
      // Migrate the custom logo, if defined.
      $logo_path = $theme_config->get('logo.path');
      if ($logo_path) {
        $speedway->set('logo.use_default', FALSE);
        $speedway->set('logo.path', $logo_path);
      }
      else {
        $speedway->set('logo.use_default', TRUE);
      }
      // Save additional theme settings.
      if (isset($link) && isset($title)) {
        $speedway->set('parent_link', $link);
        $speedway->set('parent_link_title', $title);
        $speedway->save();
      }
      if (isset($logo_height)) {
        $speedway->set('logo_height', $logo_height);
        $speedway->save();
      }
      if (isset($header_secondary_display)) {
        $speedway->set('header_secondary_display', $header_secondary_display);
        $speedway->save();
      }
      if (isset($main_menu_alignment)) {
        $speedway->set('main_menu_alignment', $main_menu_alignment);
        $speedway->save();
      }
      $theme_info = \Drupal::service('theme.initialization')->getActiveThemeByName($default_theme);
      foreach ($theme_info->getBaseThemeExtensions() as $base_theme) {
        $base_theme = $base_theme->getName();
        if ($base_theme === 'speedway') {
          // Delete required links if default theme is a sub-theme of Speedway.
          $blocks = \Drupal::entityTypeManager()->getStorage('block')
            ->loadByProperties(['plugin' => 'required_links_block', 'theme' => $default_theme]);
          foreach ($blocks as $block) {
            $block->delete();
          }
          \Drupal::configFactory()->getEditable('block.block.required_links_block')->delete();
        }
      }
      // Delete required links from Speedway.
      $blocks = \Drupal::entityTypeManager()->getStorage('block')
        ->loadByProperties(['plugin' => 'required_links_block', 'theme' => 'speedway']);
      foreach ($blocks as $block) {
        $block->delete();
      }
      \Drupal::configFactory()->getEditable('block.block.required_links_block')->delete();
    }
  }

  /**
   * Implements hook_preprocess_views_view_table().
   */
  #[Hook('preprocess_views_view_table')]
  public function preprocessViewsViewTable(&$variables) {
    // Override this with a sub-theme preprocess hook that removes the class.
    $variables['attributes']['class'][] = 'border-1';
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_block_alter')]
  public function themeSuggestionsBlockAlter(array &$suggestions, array $variables) {
    // Remove the block and replace dashes with underscores in the block ID to
    // use for the hook name.
    // @todo Once no sites are using Forty Acres, we can remove this hook,
    // since Speedway does not use it.
    $base_plugin_id = $variables['elements']['#base_plugin_id'];
    if (isset($base_plugin_id) && in_array($base_plugin_id, ['system_menu_block', 'menu_block'])) {
      if (isset($variables['elements']['#id'])) {
        $hook = $variables['elements']['#id'];
        $block = Block::load($hook);
        if (!empty($block)) {
          $region = $block->getRegion();
          $suggestions[] = 'block__system_menu_block__' . $region;
        }
      }
    }
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_menu_alter')]
  public function themeSuggestionsMenuAlter(array &$suggestions, array $variables) {
    if (isset($variables['attributes']['menu-block-id'])) {
      if ($block = Block::load($variables['attributes']['menu-block-id'])) {
        $region = $block->getRegion();
        $suggestions[] = 'menu__' . $region;
      }
    }
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_page_alter')]
  public function themeSuggestionsPageAlter(array &$suggestions, array $variables) {
    // Add content type suggestions.
    // @todo Once no sites are using Forty Acres, we can remove this hook,
    // since Speedway does not use it.
    if ($node = \Drupal::request()->attributes->get('node')) {
      if ($node instanceof NodeInterface) {
        array_splice($suggestions, 1, 0, 'page__node__' . $node->getType());
      }
      else {
        $node_revision = \Drupal::entityTypeManager()->getStorage('node')->load($node);
        if ($node_revision instanceof NodeInterface) {
          array_splice($suggestions, 1, 0, 'page__node__' . $node_revision->getType());
        }
      }
    }
  }

  /**
   * Implements hook_theme_registry_alter().
   */
  #[Hook('theme_registry_alter')]
  public function themeRegistryAlter(&$theme_registry) {
    $utexas = \Drupal::service('extension.list.profile')->getPath('utexas');
    $theme_registry['feed_block_rss_item']['path'] = $utexas . '/templates';
  }

  /**
   * Implements hook_toolbar().
   */
  #[Hook('toolbar')]
  public function toolbar() {
    return \Drupal::service('class_resolver')
      ->getInstanceFromDefinition(ToolbarHandler::class)
      ->toolbar();
  }

  /**
   * Implements hook_user_format_name_alter().
   */
  #[Hook('user_format_name_alter')]
  public function userFormatNameAlter(&$name, AccountInterface $account) {
    $uid = $account->id();
    // Don't alter anonymous users or objects that do not have any user ID.
    if (empty($uid)) {
      return;
    }
    $user = User::load($uid);
    if ($user && $user->hasField('field_utexas_full_name')) {
      if ($value = ($user->get('field_utexas_full_name')->getString())) {
        // Alter the name only if it is a non-empty string.
        if (mb_strlen($value)) {
          $name = $value;
        }
      }
      return;
    }
  }

}
