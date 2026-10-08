<?php

namespace Drupal\utexas_featured_highlight\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_featured_highlight' formatter.
 */
#[FieldFormatter(
  id: 'utexas_featured_highlight_2',
  label: new TranslatableMarkup('Bluebonnet (Medium)'),
  field_types: ['utexas_featured_highlight']
)]
class UTexasFeaturedHighlightMediumFormatter extends UTexasFeaturedHighlightDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#style'] = 'medium';
    }
    return $elements;
  }

}
