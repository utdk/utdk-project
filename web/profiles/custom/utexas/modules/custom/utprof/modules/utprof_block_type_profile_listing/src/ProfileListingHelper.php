<?php

namespace Drupal\utprof_block_type_profile_listing;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Database\Database;
use Drupal\node\NodeInterface;
use Drupal\views\Views;

/**
 * Business logic for rendering the listing view.
 */
class ProfileListingHelper {

  /**
   * Inspect block field & return a view mode.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   The block object.
   *
   * @return string
   *   The user-selected view mode.
   */
  public static function getViewMode(BlockContent $block_content) {
    $view_mode = 'utexas_basic';
    if ($block_content->hasField('field_utprof_view_mode') && !$block_content->get('field_utprof_view_mode')->isEmpty()) {
      $raw_view_mode = $block_content->get('field_utprof_view_mode')->first()->getValue();
      $prefixed_view_mode_id = $raw_view_mode['target_id'];
      // Remove entity_type_id prefix ('node.' in this case).
      $view_mode = substr($prefixed_view_mode_id, strpos($prefixed_view_mode_id, '.') + 1);
    }
    return $view_mode;
  }

  /**
   * Inspect block fields & return Views-type filter array.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   The block object.
   *
   * @return array
   *   A Views-type options array.
   */
  public static function generateFilters(BlockContent $block_content) {
    $user_defined_filters['field_utprof_profile_groups_target_id'] = [];
    $user_defined_filters['field_utprof_profile_tags_target_id'] = [];

    if ($block_content->hasField('field_utprof_profile_groups')) {
      $groups = $block_content->get('field_utprof_profile_groups')->getValue();
      foreach ($groups as $group) {
        $target_id = $group['target_id'];
        if (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($target_id)) {
          $user_defined_filters['field_utprof_profile_groups_target_id'][] = $target_id;
        }
      }
    }
    if ($block_content->hasField('field_utprof_profile_tags')) {
      $groups = $block_content->get('field_utprof_profile_tags')->getValue();
      foreach ($groups as $group) {
        $target_id = $group['target_id'];
        if (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($target_id)) {
          $user_defined_filters['field_utprof_profile_tags_target_id'][] = $target_id;
        }
      }
    }
    return $user_defined_filters;
  }

  /**
   * Generate a renderable list based on editor-chosen profiles.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   The block object.
   *
   * @return array
   *   A render array.
   */
  public static function buildList(BlockContent $block) {
    $view_mode = self::getViewMode($block);
    $display_format = str_replace('utexas_', '', $view_mode);
    if (!$block->hasField('field_utprof_specific_profiles')) {
      return FALSE;
    }
    $profiles = $block->get('field_utprof_specific_profiles')->getValue();
    if (empty($profiles)) {
      return FALSE;
    }
    $output = [];
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    foreach ($profiles as $profile) {
      $node = $storage->load($profile['target_id']);
      if (!$node instanceof NodeInterface) {
        // The referenced node was likely deleted (#295).
        continue;
      }
      if (!$node->isPublished()) {
        continue;
      }
      $build = $view_builder->view($node, $view_mode);
      $output[] = [
        '#wrapper_attributes' => ['class' => 'utprof__profile-item utprof__profile-item-' . $display_format],
        '#markup' => \Drupal::service('renderer')->render($build),
      ];
    }
    $content = [
      '#theme' => 'item_list',
      '#type' => 'ul',
      '#items' => $output,
      '#attributes' => ['class' => 'utprof__views-list'],
      '#wrapper_attributes' => ['class' => 'container'],
    ];
    return $content;
  }

  /**
   * Generate a renderable View based on user input.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   The block object.
   *
   * @return array
   *   A render array.
   */
  public static function buildContextualView(BlockContent $block_content) {
    $utprof_profile_listing_view_display = 'block_utprof_profile_listing';
    $user_defined_filters = self::generateFilters($block_content);
    $view_mode = self::getViewMode($block_content);
    $display_format = str_replace('utexas_', '', $view_mode);
    $view = Views::getView('utprof_profiles');
    if (is_object($view)) {
      // Specify which Views display to use.
      $view->setDisplay($utprof_profile_listing_view_display);
      $row_class = $view->display_handler->default_display->options['style']['options']['row_class'];
      $view->display_handler->default_display->options['style']['options']['row_class'] = $row_class . ' ' . $row_class . '-' . $display_format;
      // Set view mode based on user-provided value.
      $row = $view->display_handler->getOption('row');
      $row['options']['view_mode'] = $view_mode;
      $view->display_handler->overrideOption('row', $row);

      // Set filters based on user-provided values.
      $filters = $view->display_handler->getOption('filters');
      foreach ($user_defined_filters as $field => $values) {
        if ($filters[$field]) {
          // Reset filters initially.
          $filters[$field]['value'] = [];
          foreach ($values as $value) {
            // Incrementally restrict filters ("OR" logic).
            $filters[$field]['value'][$value] = $value;
          }
        }
      }
      $view->display_handler->overrideOption('filters', $filters);
      $view->preExecute();
      $view->execute();
      return $view->preview($utprof_profile_listing_view_display);
    }
    return FALSE;
  }

  /**
   * Given a block type and a view_mode/style map, add appropriate style.
   *
   * IMPORTANT: This function is intended to affect *all* node revisions.
   * The use case is limited to when a Layout Builder Style needs to be added
   * retroactively to *preserve* behavior on existing sites.
   *
   * @param string $block_type
   *   The machine name of a block type.
   * @param array $style_map
   *   An array with the view mode machine name as keys and the Layout Builder
   *   Style machine name as values.
   */
  public static function mapViewModeToStyle($block_type, array $style_map) {
    /** @var \Drupal\Core\Database\Connection $connection */
    $connection = Database::getConnection();
    /** @var \Drupal\Core\Logger\LoggerChannelInterface $logger */
    $logger = \Drupal::logger('utexas_layout_builder_styles');
    /** @var \Drupal\Core\Entity\EntityStorageInterface $block_content_storage */
    $block_content_storage = \Drupal::entityTypeManager()->getStorage('block_content');
    /** @var \Drupal\Core\Entity\EntityRepository $entity_repository*/
    $entity_repository = \Drupal::service('entity.repository');

    $tables = [
      'node__layout_builder__layout',
      'node_revision__layout_builder__layout',
    ];
    foreach ($tables as $table) {
      $query = $connection->query("SELECT * FROM {" . $table . "}");
      $result = $query->fetchAll();
      foreach (array_values($result) as $row) {
        $affected = FALSE;
        /** @var \Drupal\layout_builder\Section $section */
        // This serialized data is trusted from Layout Builder,
        // so we do not restrict object types in unserialize().
        // phpcs:ignore
        $section = unserialize($row->layout_builder__layout_section);
        $components = $section->getComponents();
        /** @var \Drupal\layout_builder\SectionComponent $component */
        foreach (array_values($components) as $component) {
          $config = $component->get('configuration');
          $config_provider = $config['provider'];

          switch ($config_provider) {
            // Reusable block.
            case 'block_content':
              // Load the block entity using uuid in configuration array.
              $uuid = str_replace('block_content:', '', $config['id']);
              $block_content = $entity_repository->loadEntityByUuid('block_content', $uuid);
              if (!$block_content) {
                // The block cannot be loaded (likely deleted). Skip.
                continue 2;
              }
              // If block bundle does not match specified block type, skip.
              if ($block_content->bundle() !== $block_type) {
                continue 2;
              }
              break;

            // Inline block.
            case 'layout_builder':
              // If the component is not of the specified block type, skip.
              if (strpos($component->getPluginId(), $block_type) === FALSE) {
                continue 2;
              }
              // Load the block entity using revision_id in configuration array.
              $revision_id = $config['block_revision_id'];
              $block_content = $block_content_storage->loadRevision($revision_id);
              break;

            // If it's neither provider, skip.
            default:
              continue 2;
          }

          // Get view mode value for key in mapping.
          $view_mode = self::getViewMode($block_content);

          // If key does not exist in our mapping, skip.
          if (!array_key_exists($view_mode, $style_map)) {
            continue;
          }

          $affected = TRUE;
          $style = $style_map[$view_mode];
          $additional = $component->get('additional');
          $additional['layout_builder_styles_style'][$style] = $style;
          $component->set('additional', $additional);
        }
        // If any components have been updated, update their db record.
        if ($affected) {
          $connection->update($table)
            ->fields([
              'layout_builder__layout_section' => serialize($section),
            ])
            ->condition('entity_id', $row->entity_id, '=')
            ->condition('revision_id', $row->revision_id, '=')
            ->condition('delta', $row->delta, '=')
            ->execute();
          if ($table === 'node__layout_builder__layout') {
            $logger->notice('Updated Layout Builder Styles using view modes in node/' . $row->entity_id . ' ' . $block_type . ' block instances.');
          }
        }
      }
    }
  }

}
