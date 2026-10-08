<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_promo_list' formatter.
 */
#[FieldFormatter(
  id: 'utexas_promo_list_3',
  label: new TranslatableMarkup('Two lists, side-by-side'),
  field_types: ['utexas_promo_list']
)]
class UTexasPromoListTwoColumnFormatter extends UTexasPromoListDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $item) {
      if (gettype($delta) === 'integer') {
        $elements[$delta]['#wrapper'] = 'two-side-by-side';
        $elements['#items'][$delta] = new \stdClass();
        $elements['#items'][$delta]->_attributes = [
          'class' => ['ut-promo-list-side-by-side-wrapper'],
        ];
      }
    }
    return $elements;
  }

}
