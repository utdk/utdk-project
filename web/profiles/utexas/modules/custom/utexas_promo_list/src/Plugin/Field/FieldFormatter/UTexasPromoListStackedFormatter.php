<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_promo_list' formatter.
 */
#[FieldFormatter(
  id: 'utexas_promo_list_4',
  label: new TranslatableMarkup('Single list stacked'),
  field_types: ['utexas_promo_list']
)]
class UTexasPromoListStackedFormatter extends UTexasPromoListDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements['#items'][$delta] = new \stdClass();
      $elements['#items'][$delta]->_attributes = [
        'class' => ['stacked-display'],
      ];
    }
    return $elements;

  }

}
