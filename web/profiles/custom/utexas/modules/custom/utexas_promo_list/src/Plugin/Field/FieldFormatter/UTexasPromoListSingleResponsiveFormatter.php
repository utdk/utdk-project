<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_promo_list' formatter.
 */
#[FieldFormatter(
  id: 'utexas_promo_list_2',
  label: new TranslatableMarkup('Single list responsive (2 items per row)'),
  field_types: ['utexas_promo_list']
)]
class UTexasPromoListSingleResponsiveFormatter extends UTexasPromoListDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $item) {
      if (gettype($delta) === 'integer') {
        $elements[$delta]['#wrapper'] = 'two-column-responsive';
      }
    }
    return $elements;
  }

}
