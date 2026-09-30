<?php

namespace Drupal\utexas_missing_blocks;

use Drupal\layout_builder\Section;

/**
 * Resave inline blocks associated with cloned nodes.
 */
class Defuser {

  /**
   * Main callback.
   *
   * @return array
   *   Returns a map for reference while traversing layout revisions.
   */
  public static function defuse() {
    // Get extant site data for nodes & blocks.
    $layout_revisions = self::getAllLayoutRevisions();
    $block_map = self::getExistingBlocks();
    $inline_block_usage = self::getInlineBlockUsage();

    // Prepare a map for reference as we traverse layout revisions.
    $defused = [];
    /*
     * Prepare to store a map of blocks, keyed by node id, then original
     * block id, e.g.:
     * '<node_id> => [
     *   'cloned_from_node' => 35,
     *   'revisions_with_missing_blocks' => [75, 76],
     *   '<old_bid>' => [
     *    'new_bid' => 10,
     *    'rids' => [
     *       '<old_rid>' => 11,
     *       '<old_rid>' => 12,
     *     ],
     *   ],
     * ];
     */
    foreach ($layout_revisions as $current_node) {
      // Start by assuming this layout revision doesn't need any changes.
      $do_save = FALSE;
      // This serialized data is trusted from Layout Builder,
      // so we do not restrict object types in unserialize().
      // phpcs:ignore
      $section = unserialize($current_node->layout_builder__layout_section);
      $components = $section->getComponents();
      $section_array = $section->toArray();
      $new_section_array = $section_array;
      // Check each component in the current section.
      foreach ($components as &$component) {
        $component_array = $component->toArray();
        if (empty($component_array['configuration']['provider']) || $component_array['configuration']['provider'] !== 'layout_builder') {
          // Skip anything other than blocks.
          continue;
        }
        $plugin = $component->getPlugin();
        $deriver_id = $plugin->getPluginDefinition()['id'];
        // Only process inline blocks (i.e., filter out reusable block_content).
        if ($deriver_id !== 'inline_block') {
          continue;
        }
        $uuid = $component_array['uuid'];
        // This is the block revision ID originally used by the clone, e.g. "3".
        $old_rid = $component_array['configuration']['block_revision_id'];
        // Get original block ID to create a duplicate.
        $old_bid = $block_map[$old_rid] ?? 0;
        $originating_node_id = $inline_block_usage[$old_bid];
        // Check if this inline block is referenced by another entity.
        if (isset($inline_block_usage[$old_bid]) && $current_node->entity_id === $originating_node_id) {
          // This inline block was created on the entity itself. Move on.
          continue;
        }
        // Check if the block still exists and can be cloned.
        if ($old_bid === 0) {
          // This block has been deleted and we can't recover it.
          $defused[$current_node->entity_id]['revisions_with_missing_blocks'][] = 'Revision ' . $current_node->revision_id;
          continue;
        }
        // This inline block was created from a different entity and it is
        // still recoverable. Create a duplicate inline block revision and add
        // to our map.
        $defused = self::duplicateBlockRevision($old_rid, $old_bid, $defused, $current_node->entity_id, $originating_node_id);
        // Update the node component reference to the block revision id.
        if (isset($defused[$current_node->entity_id][$old_bid]['rids'][$old_rid])) {
          $component_array['configuration']['block_revision_id'] = $defused[$current_node->entity_id][$old_bid]['rids'][$old_rid];
          $new_section_array['components'][$uuid] = $component_array;
          $do_save = TRUE;
        }
      }
      // Only overwrite the layout if we found something to change.
      if ($do_save) {
        $new_section = Section::fromArray($new_section_array);
        // Write the change in the layout storage to the database.
        self::saveNewSection($current_node->entity_id, $current_node->revision_id, $current_node->delta, $new_section);
      }
    }
    // Empty the cache.entity and cache.data tables so nodes don't display
    // the old block.
    drupal_flush_all_caches();
    return $defused;
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
   * Move a shared inline block revision to a self-contained block.
   *
   * @param int $old_rid
   *   The originating block revision ID.
   * @param int $old_bid
   *   The originating block ID.
   * @param array $defused
   *   The migration map of defused blocks.
   * @param int $referencing_entity_id
   *   The node ID this block is referenced on.
   * @param int $originating_node_id
   *   The node ID that this block was cloned from.
   *
   * @return array
   *   The updated $defused map.
   */
  public static function duplicateBlockRevision($old_rid, $old_bid, array $defused, $referencing_entity_id, $originating_node_id) {
    $block_storage = \Drupal::entityTypeManager()->getStorage('block_content');
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $referencing_entity = $node_storage->load($referencing_entity_id);
    /** @var \Drupal\block_content\BlockContentInterface $old_entity_revision */
    $old_entity_revision = $block_storage->loadRevision($old_rid);
    // Check if we already have a new block for the clone.
    if (isset($defused[$referencing_entity_id][$old_bid])) {
      // We already created a new block for this. Load that to get the base
      // properties.
      $already_registered = TRUE;
      /** @var \Drupal\block_content\BlockContentInterface $new_entity */
      $new_entity = $block_storage->load($defused[$referencing_entity_id][$old_bid]['new_bid']);
      $new_entity->setNewRevision();
      $new_entity->setRevisionTranslationAffectedEnforced(TRUE);
    }
    else {
      $already_registered = FALSE;
      /** @var \Drupal\block_content\BlockContentInterface $new_entity */
      $new_entity = $old_entity_revision->createDuplicate();
    }
    foreach (array_keys($new_entity->getFieldDefinitions()) as $field_id) {
      $field = $old_entity_revision->get($field_id);
      $field_revision_value = $field->getValue();
      if (!in_array($field_id, ['id', 'uuid', 'revision_id', 'changed'])) {
        $new_entity->set($field_id, $field_revision_value);
      }
    }

    $new_entity->save();
    $new_bid = $new_entity->id();
    $new_rid = $new_entity->getRevisionId();

    if (!$already_registered) {
      // Register this new block in Inline Block Usage.
      /** @var \Drupal\layout_builder\InlineBlockUsageInterface $inline_block_usage */
      $inline_block_usage = \Drupal::service('inline_block.usage');
      $inline_block_usage->addUsage($new_bid, $referencing_entity);
    }

    // Add to the map so that the next revision can be added to the new block.
    if (!isset($defused[$referencing_entity_id][$old_bid])) {
      $defused[$referencing_entity_id][$old_bid] = [
        'new_bid' => $new_bid,
        'rids' => [],
      ];
      $defused[$referencing_entity_id]['cloned_from_node'] = $originating_node_id;
    }
    $defused[$referencing_entity_id][$old_bid]['rids'][$old_rid] = $new_rid;
    return $defused;
  }

  /**
   * Get current inline block usage.
   *
   * @return array
   *   A list of all inline block usage by BID.
   */
  public static function getInlineBlockUsage() {
    $map = [];
    $connection = \Drupal::database();
    $query = $connection->select('inline_block_usage', 'b');
    $query->fields('b', ['block_content_id', 'layout_entity_id']);
    $result = $query->execute();
    $usage = $result->fetchAll();
    foreach ($usage as $b) {
      $map[$b->block_content_id] = $b->layout_entity_id;
    }
    return $map;
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
