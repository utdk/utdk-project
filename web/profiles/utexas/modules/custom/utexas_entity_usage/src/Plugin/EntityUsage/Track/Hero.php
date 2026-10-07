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
    id: 'utexas_hero',
    label: new TranslatableMarkup('UTexas Hero'),
    description: new TranslatableMarkup("Tracks relationships created with 'Hero' fields."),
    field_types: ['utexas_hero'],
    source_entity_class: 'Drupal\Core\Entity\FieldableEntityInterface'
  )
]
class Hero extends UtexasEntityUsageTrackBase {

  /**
   * {@inheritdoc}
   */
  public function getTargetEntities(FieldItemInterface $item): array {
    $references = [];
    $value = $item->getValue();
    // The entity_usage module is designed to execute implementations of
    // getTargetEntities() on each delta of a field instance; since our custom
    // field types that have media upload fields only allow a single media item
    // at a time, we can safely assume that the media value below is always a
    // a single integer, not a string.
    if (isset($value['media'])) {
      $target = (string) $value['media'];
      $references[] = 'media|' . $target;
    }
    return $references;
  }

}
