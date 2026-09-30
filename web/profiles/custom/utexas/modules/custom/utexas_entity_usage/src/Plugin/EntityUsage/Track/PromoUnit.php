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
    id: 'utexas_promo_unit',
    label: new TranslatableMarkup('UTexas Promo Unit'),
    description: new TranslatableMarkup("Tracks relationships created with 'Promo Unit' fields."),
    field_types: ['utexas_promo_unit'],
    source_entity_class: 'Drupal\Core\Entity\FieldableEntityInterface'
  )
]
class PromoUnit extends UtexasEntityUsageTrackBase {

  /**
   * {@inheritdoc}
   */
  public function getTargetEntities(FieldItemInterface $item): array {
    $references = [];
    $value = $item->getValue();
    if (isset($value['promo_unit_items'])) {
      // We can safely unserialize Promo Unit items.
      // phpcs:ignore
      $promo_unit_items = unserialize($value['promo_unit_items']);
      foreach ($promo_unit_items as $item) {
        // The entity_usage module is designed to execute implementations of
        // getTargetEntities() on each delta of a field instance; since our
        // custom field types that have media upload fields only allow a single
        // media item at a time, we can safely assume that the media value below
        // is always a single integer, not a string.
        if (isset($item['item']['image'])) {
          $references[] = 'media|' . $item['item']['image'];
        }
        // Process media entities references in copy field.
        // UtexasEntityUsageTrackBase::parseMediaFromText() largely replicates
        // logic from the entity_usage module's
        // MediaEmbed::parseEntitiesFromText().
        $copy = $item['item']['copy']['value'] ?? NULL;
        if (!is_null($copy)) {
          $media_from_copy = $this->parseMediaFromText($copy);
          foreach ($media_from_copy as $media) {
            $references[] = $media;
          }
        }
      }
    }
    return $references;
  }

}
