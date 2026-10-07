<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\utexas_form_elements\RenderElementHelper;

/**
 * Base class for 'UTexas Flex List Field formatter' plugin implementations.
 *
 * @ingroup field_formatter
 */
abstract class UTexasFlexListFormatterBase extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        'header' => RenderElementHelper::filterSingleLineText($item->header, TRUE),
        'id' => [
          '#plain_text' => Html::getUniqueId($item->header),
        ],
        'body' => [
          '#type' => 'processed_text',
          '#text' => $item->content_value,
          '#format' => $item->content_format,
          '#langcode' => $item->getLangcode(),
        ],
      ];
    }
    $elements['#theme_info'] = ['formatter_name' => 'default'];
    return $elements;
  }

}
