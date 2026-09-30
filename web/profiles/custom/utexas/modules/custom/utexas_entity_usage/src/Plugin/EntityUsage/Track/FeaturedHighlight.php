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
    id: 'utexas_featured_highlight',
    label: new TranslatableMarkup('UTexas Featured Highlight'),
    description: new TranslatableMarkup("Tracks relationships created with 'Featured Highlight' fields."),
    field_types: ['utexas_featured_highlight'],
    source_entity_class: 'Drupal\Core\Entity\FieldableEntityInterface'
  )
]
class FeaturedHighlight extends UtexasEntityUsageTrackBase {

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
    // Process media entities references in copy field.
    // UtexasEntityUsageTrackBase::parseMediaFromText() largely replicates logic
    // from the entity_usage module's MediaEmbed::parseEntitiesFromText().
    $copy = $value['copy_value'] ?? NULL;
    if (!is_null($copy)) {
      $media_from_copy = $this->parseMediaFromText($copy);
      foreach ($media_from_copy as $media) {
        $references[] = $media;
      }
    }
    return $references;
  }

}
