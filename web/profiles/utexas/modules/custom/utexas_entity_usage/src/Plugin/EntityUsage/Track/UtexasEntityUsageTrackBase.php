<?php

namespace Drupal\utexas_entity_usage\Plugin\EntityUsage\Track;

use Drupal\entity_usage\EntityUsageTrackBase;
use Drupal\Component\Utility\Html;

/**
 * Base implementation for track plugins.
 */
abstract class UtexasEntityUsageTrackBase extends EntityUsageTrackBase {

  /**
   * {@inheritdoc}
   */
  public function parseMediaFromText($text) {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    $entities = [];
    foreach ($xpath->query('//drupal-media[@data-entity-type="media" and @data-entity-uuid]') as $node) {
      assert($node instanceof \DOMElement);
      // Skip elements with empty data-entity-uuid attributes.
      if (empty($node->getAttribute('data-entity-uuid'))) {
        continue;
      }
      $entity_type_id = $node->getAttribute('data-entity-type');
      if ($this->isEntityTypeTracked($entity_type_id)) {
        $entities[$node->getAttribute('data-entity-uuid')] = $entity_type_id;
      }
    }
    $valid_entities = [];
    $uuids_by_type = [];
    foreach ($entities as $uuid => $entity_type) {
      $uuids_by_type[$entity_type][] = $uuid;
    }
    foreach ($uuids_by_type as $entity_type => $uuids) {
      $target_type = $this->entityTypeManager->getDefinition($entity_type);
      // Check if the target entity exists since text fields are not
      // automatically updated when an entity is removed.
      $query = $this->entityTypeManager->getStorage($entity_type)
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition($target_type->getKey('uuid'), $uuids, 'IN');
      $valid_entities = array_merge($valid_entities, array_values(array_unique(array_map(fn($id) => $entity_type . '|' . $id, $query->execute()))));
    }
    return $valid_entities;
  }

}
