<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 */
#[FieldFormatter(
  id: 'utexas_promo_unit_3',
  label: new TranslatableMarkup('Square (140x140, 1:1 ratio)'),
  field_types: ['utexas_promo_unit'],
  weight: 2
)]
class UTexasPromoUnitSquareFormatter extends UTexasPromoUnitDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $cache_tags = $this->generateCacheTags('utexas_responsive_image_pu_square');
    foreach ($elements as $delta => $item) {
      $promo_unit_items = unserialize($items[$delta]->promo_unit_items);
      if (!empty($item['#promo_unit_items'])) {
        foreach ($item['#promo_unit_items'] as $key => &$instance) {
          $image = $promo_unit_items[$key]['item']['image'] ?? FALSE;
          if (!empty($image)) {
            $responsive_image_style_name = 'utexas_responsive_image_pu_square';
            $item['#promo_unit_items'][$key]['image'] = $this->generateImageRenderArray($image, $responsive_image_style_name, $cache_tags);
          }
        }
      }
      $elements[$delta]['#promo_unit_items'] = $item['#promo_unit_items'];
      $elements[$delta]['#image_display'] = 'square-image';
    }
    return $elements;
  }

}
