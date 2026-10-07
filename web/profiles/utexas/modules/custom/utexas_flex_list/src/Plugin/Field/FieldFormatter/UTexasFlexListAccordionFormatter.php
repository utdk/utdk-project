<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_flex_list_accordion' formatter.
 */
#[FieldFormatter(
  id: 'utexas_flex_list_accordion',
  label: new TranslatableMarkup('Accordion'),
  field_types: ['utexas_flex_list']
)]
class UTexasFlexListAccordionFormatter extends UTexasFlexListFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $elements['#theme_info'] = ['formatter_name' => 'accordion'];
    return $elements;
  }

}
