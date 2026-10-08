<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 */
#[FieldFormatter(
  id: 'utexas_promo_unit_4',
  label: new TranslatableMarkup('Stacked Landscape (1.6:1 ratio)'),
  field_types: ['utexas_promo_unit'],
  weight: 4
)]
class UTexasPromoUnitLandscapeStackedFormatter extends UTexasPromoUnitDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $item) {
      foreach ($items as $delta => $item) {
        $elements['#items'][$delta] = new \stdClass();
        $elements['#items'][$delta]->_attributes = [
          'class' => ['stacked-display'],
        ];
      }
    }
    return $elements;
  }

}
