<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 */
#[FieldFormatter(
  id: 'utexas_hero_5_left',
  label: new TranslatableMarkup('Style 5: Medium image, floated right, with large heading, subheading and burnt orange call-to-action, image anchored left'),
  field_types: ['utexas_hero']
)]
class UTexasHeroStyle5FormatterLeft extends UTexasHeroStyle5Formatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#anchor_position'] = 'left';
    }
    return $elements;
  }

}
