<?php

namespace Drupal\utexas_missing_blocks;

use Drupal\layout_builder\Section;

/**
 * Delete inline blocks of a specific type.
 */
class BlockLayoutReferenceDeleter {

  /**
   * Main callback.
   *
   * @param string $bundle
   *   The name of a block bundle to delete.
   *
   * @return array
   *   Returns a map for reference while traversing layout revisions.
   */
  public static function delete($bundle) {
    $data = [
      'layout' => [],
      'nodes' => [],
    ];
    // Get extant site data for nodes & blocks.
    $layout_revisions = self::getAllLayoutRevisions();
    /** @var \Drupal\layout_builder\InlineBlockUsageInterface $inline_block_usage */
    $inline_block_usage = \Drupal::service('inline_block.usage');
    $bundle_uuids = self::getExistingBlocksByBundle($bundle);
    $block_map = self::getExistingBlocks();

    foreach ($layout_revisions as $current_node) {
      // Start by assuming this layout revision doesn't need any changes.
      $do_save = FALSE;
      // This serialized data is trusted from Layout Builder,
      // so we do not restrict object types in unserialize().
      // phpcs:ignore
      $section = unserialize($current_node->layout_builder__layout_section);
      $components = $section->getComponents();
      $section_array = $section->toArray();
      // Check each component in the current section.
      foreach ($components as &$component) {
        $component_array = $component->toArray();
        $layout_uuid = $component_array['uuid'];
        // Handle reusable blocks.
        if (isset($component_array['configuration']['id'])) {
          $parts = explode(':', $component_array['configuration']['id']);
          if (isset($parts[1]) && in_array($parts[1], $bundle_uuids)) {
            \Drupal::logger('utexas')->notice('Deleting reusable ' . $bundle . ' block reference "' . $component_array['configuration']['label'] . '" from node ' . $current_node->entity_id . ', revision ' . $current_node->revision_id);
            $data['layout'][] = $layout_uuid;
            $data['nodes'][] = $current_node->entity_id;
            unset($section_array['components'][$layout_uuid]);
            $do_save = TRUE;
          }
        }
        if (empty($component_array['configuration']['provider']) || $component_array['configuration']['provider'] !== 'layout_builder') {
          // Skip anything other than blocks.
          continue;
        }

        // Find inline blocks.
        if ($component_array['configuration']['id'] === 'inline_block:' . $bundle) {
          \Drupal::logger('utexas')->notice('Deleting inline ' . $bundle . ' block reference "' . $component_array['configuration']['label'] . '" from node ' . $current_node->entity_id . ', revision ' . $current_node->revision_id);
          $data['layout'][] = $layout_uuid;
          $data['nodes'][] = $current_node->entity_id;
          unset($section_array['components'][$layout_uuid]);
          // Remove this new block in Inline Block Usage.
          $rid = $component_array['configuration']['block_revision_id'];
          $bid = $block_map[$rid] ?? 0;
          $inline_block_usage->deleteUsage([$bid]);
          $do_save = TRUE;
        }
      }
      // Only overwrite the layout if we found something to change.
      if ($do_save) {
        $new_section = Section::fromArray($section_array);
        // Write the change in the layout storage to the database.
        self::saveNewSection($current_node->entity_id, $current_node->revision_id, $current_node->delta, $new_section);
      }
    }
    // Empty the cache.entity and cache.data tables so nodes don't display
    // the old block.
    drupal_flush_all_caches();
    return $data;
  }

  /**
   * Get a map of extant blocks matching "bundle".
   *
   * @param string $bundle
   *   The block bundle.
   *
   * @return array
   *   A list of all blocks, keyed by UUID.
   */
  public static function getExistingBlocksByBundle($bundle) {
    $connection = \Drupal::database();
    $query = $connection->select('block_content', 'b');
    $query->condition('b.type', $bundle);
    $query->fields('b', ['uuid']);
    $result = $query->execute();
    $uuids = $result->fetchCol(0);
    return $uuids;
  }

  /**
   * Get a map of extant blocks.
   *
   * @return array
   *   A list of all blocks, keyed by revision ID.
   */
  public static function getExistingBlocks() {
    $map = [];
    $connection = \Drupal::database();
    $query = $connection->select('block_content_field_revision', 'b');
    $query->fields('b', ['id', 'revision_id']);
    $result = $query->execute();
    $block_revisions = $result->fetchAll();
    foreach ($block_revisions as $b) {
      $map[$b->revision_id] = $b->id;
    }
    return $map;
  }

  /**
   * Write a new layout section to the database.
   *
   * @param int $entity_id
   *   The entity id of the layout section to be saved.
   * @param int $revision_id
   *   The entity id of the layout section to be saved.
   * @param int $delta
   *   The entity id of the layout section to be saved.
   * @param object $section
   *   The Section object of the current layout.
   */
  protected static function saveNewSection($entity_id, $revision_id, $delta, $section) {
    $connection = \Drupal::database();
    // Update the current revision.
    $connection->update('node__layout_builder__layout')
      ->fields([
        'layout_builder__layout_section' => serialize($section),
      ])
      ->condition('entity_id', $entity_id, '=')
      ->condition('revision_id', $revision_id, '=')
      ->condition('delta', $delta, '=')
      ->execute();
    // Update all revisions.
    $connection->update('node_revision__layout_builder__layout')
      ->fields([
        'layout_builder__layout_section' => serialize($section),
      ])
      ->condition('entity_id', $entity_id, '=')
      ->condition('revision_id', $revision_id, '=')
      ->condition('delta', $delta, '=')
      ->execute();
    // Clear tempstore values to prevent outdated references.
    $connection->delete('key_value_expire')
      ->condition('collection', 'tempstore.shared.layout_builder.section_storage.overrides')
      ->condition('name', '%' . $connection->escapeLike('node.' . $entity_id) . '%', 'LIKE')
      ->execute();
  }

  /**
   * Get all Layout Builder layout revisions.
   *
   * @return array
   *   A list of all layout revisions.
   */
  public static function getAllLayoutRevisions() {
    $connection = \Drupal::database();
    $query = $connection->select('node_revision__layout_builder__layout', 'n');
    $query->fields('n', [
      'entity_id',
      'revision_id',
      'delta',
      'layout_builder__layout_section',
    ]);
    $result = $query->execute();
    $layouts = $result->fetchAll();
    return $layouts;
  }

}
