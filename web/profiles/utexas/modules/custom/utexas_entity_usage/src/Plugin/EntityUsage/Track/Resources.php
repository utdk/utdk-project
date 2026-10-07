<?php

namespace Drupal\utexas_entity_usage\Plugin\EntityUsage\Track;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_usage\Attribute\EntityUsageTrack;

/**
 * Tracks usage of entities referenced in a custom field type.
 */
#[
  EntityUsageTrack(
    id: 'utexas_resources',
    label: new TranslatableMarkup('UTexas Resources'),
    description: new TranslatableMarkup("Tracks relationships created with 'Resources' fields."),
    field_types: ['utexas_resources'],
    source_entity_class: 'Drupal\Core\Entity\FieldableEntityInterface'
  )
]
class Resources extends UtexasEntityUsageTrackBase {

  /**
   * {@inheritdoc}
   */
  public function getTargetEntities(FieldItemInterface $item): array {
    $references = [];
    $value = $item->getValue();
    if (isset($value['resource_items'])) {
      // We can safely unserialize Resource items.
      // phpcs:ignore
      $resource_items = unserialize($value['resource_items']);
      foreach ($resource_items as $item) {
        // The entity_usage module is designed to execute implementations of
        // getTargetEntities() on each delta of a field instance; since our
        // custom field types that have media upload fields only allow a single
        // media item at a time, we can safely assume that the media value below
        // is always a single integer, not a string.
        if (isset($item['item']['image'])) {
          $references[] = 'media|' . $item['item']['image'];
        }
      }
    }
    return $references;
  }

}
