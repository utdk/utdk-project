<?php

namespace Drupal\utexas_layouts\Hook;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utexas\ThemeHelper;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_preprocess_layout().
   */
  #[Hook('preprocess_layout')]
  public function preprocessLayout(&$variables) {
    // Pass background variable settings to the layout.
    $variables['background_blur'] = $variables['content']['#background_blur'] ?? NULL;
    $variables['background_image'] = $variables['content']['#background_image'] ?? NULL;
    $variables['background_color'] = $variables['content']['#background_color'] ?? NULL;
    // If "Full width of page" setting has NOT been applied to this layout,
    // ensure this layout displays as "Container width".
    $add_container = TRUE;
    if (isset($variables['attributes']['class']) && in_array('container-fluid', $variables['attributes']['class'])) {
      $add_container = FALSE;
    }
    if ($add_container) {
      $variables['attributes']['class'][] = 'container-xl';
    }
  }

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(&$variables) {
    $variables['#attached']['library'][] = 'utexas_layouts/layout_builder_ui';
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    $base_plugin_id = $variables['base_plugin_id'];
    // Blocks placed on layout builder pages.
    if (!isset($variables['elements']['#utexas_layouts_region']) || $variables['elements']['#utexas_layouts_region'] !== 'content') {
      // Some modules (e.g., Total Control) don't implement this hook correctly.
      return;
    }
    // Alter blocks placed in the main 'content' region on Layout Builder pages.
    if (!empty($variables['elements']['#utexas_layouts_region'])) {
      if ($base_plugin_id !== 'system_main_block' && $variables['elements']['#utexas_layouts_region'] === 'content') {
        // This block may be present on non-Layout Builder & Layout Builder
        // pages, so set the block cache to vary by page URL path.
        $variables['#cache']['contexts'][] = 'url.path';
        // This is a reusable block placed located in the 'content' region.
        // If the current page uses Layout Builder, set to 'container-xl' width.
        if (ThemeHelper::isLayoutBuilderPage()) {
          $variables['attributes']['class'][] = 'container-xl';
        }
        // If first section is set to "Readable", add the `readable` class.
        if (ThemeHelper::firstSectionIsReadable()) {
          $variables['attributes']['class'][] = 'readable';
        }
      }
    }
  }

  /**
   * Implements hook_plugin_filter_TYPE_alter().
   *
   * Suppress display of system blocks in Block Library & Layout Builder
   * when menu_blocks equivalents are present.
   */
  #[Hook('plugin_filter_block_alter')]
  public function pluginFilterBlockAlter(array &$definitions, array $extra, $consumer) {
    if (in_array($consumer, ['block_ui', 'layout_builder'])) {
      foreach ($definitions as $id => $definition) {
        // Is this a core-provided menu block?
        if ($definition['provider'] === 'system' && strpos($id, 'system_menu_block:') !== FALSE) {
          // Extract the machine name of the menu.
          $split_system_block_name = explode(':', $id);
          // Generate the menu_block equivalent key to compare.
          $menu_block_name = 'menu_block:' . $split_system_block_name[1];
          // If a menu_block equivalent exists, suppress the core menu from
          // being displayed as an available option.
          if (array_search($menu_block_name, array_keys($definitions)) !== FALSE) {
            unset($definitions[$id]);
          }
        }
      }
    }
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for layout_builder_add_block.
   *
   * @todo Keep an eye on https://www.drupal.org/project/drupal/issues/3074435
   */
  #[Hook('form_layout_builder_add_block_alter')]
  public function layoutBuilderAddBlockAlter(&$form, FormStateInterface $form_state) {
    if (isset($form['settings']['label_display'])) {
      // Uncheck the 'Display title' checkbox by default (on *new* blocks only).
      $form['settings']['label_display']['#default_value'] = FALSE;
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_node_layout_builder_form_alter')]
  public function layoutBuilderFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    $form['#attributes']['class'][] = 'container';
    // Remove "Revert layout" button on Flex Pages.
    $route_match = \Drupal::routeMatch();
    $entity_type_id = $route_match->getParameter('entity_type_id');
    if (isset($entity_type_id) && $entity_type_id !== NULL) {
      $bundle = $route_match->getParameter($entity_type_id)->bundle();
      if ($bundle === 'utexas_flex_page') {
        unset($form["actions"]["revert"]);
      }
    }
  }

  /**
   * Implements hook_block_view_alter().
   *
   * See See Drupal\utexas_layouts\EventSubscriber\BlockComponentRenderArray for
   * accompanying Event Subscriber to provide this value for blocks rendered
   * using Layout Builder.
   */
  #[Hook('block_view_alter')]
  public function blockViewAlter(array &$build, BlockPluginInterface $block) {
    if (!isset($build['#block'])) {
      // Some modules (e.g., Total Control) don't implement this hook correctly.
      return;
    }
    /** @var Drupal\block\BlockInterface $block_config_entity */
    $block_config_entity = $build['#block'];
    // Add a key/value for the region where the block is being rendered.
    $build['#utexas_layouts_region'] = $block_config_entity->getRegion();
  }

  /**
   * Implements hook_contextual_links_plugins_alter().
   */
  #[Hook('contextual_links_plugins_alter')]
  public function contextualLinksPluginAlter(array &$contextual_links) {
    // Do not allow users to access the "Remove block" link present in any
    // site-wide blocks that display on any page. Multiple users have
    // inadvertently removed a site-wide block, under the impression that they
    // would only be affecting a single page. See
    // https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/2069
    // The below will NOT remove the "Remove block" link from Layout
    // Builder blocks, since that is supplied by the separate
    // 'layout_builder_block_remove' item.
    unset($contextual_links['block_remove']);
  }

}
