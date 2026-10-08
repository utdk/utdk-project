<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 */
#[FieldFormatter(
  id: 'utexas_hero_2_right',
  label: new TranslatableMarkup('Style 2: Bold heading on dark background, anchored at base of media, image anchored right'),
  field_types: ['utexas_hero']
)]
class UTexasHeroStyle2FormatterRight extends UTexasHeroStyle2Formatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#anchor_position'] = 'right';
    }
    return $elements;
  }

}
