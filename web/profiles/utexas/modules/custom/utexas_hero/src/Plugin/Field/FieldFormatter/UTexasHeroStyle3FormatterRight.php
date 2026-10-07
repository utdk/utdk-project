<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 */
#[FieldFormatter(
  id: 'utexas_hero_3_right',
  label: new TranslatableMarkup('Style 3: White bottom pane with heading, subheading and burnt orange call to action, image anchored right'),
  field_types: ['utexas_hero']
)]
class UTexasHeroStyle3FormatterRight extends UTexasHeroStyle3Formatter {

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
