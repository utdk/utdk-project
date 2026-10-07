<?php

namespace Drupal\utexas_quick_links\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\RenderElementHelper;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;

/**
 * Plugin implementation of the 'utexas_quick_links' formatter.
 */
#[FieldFormatter(
  id: 'utexas_quick_links',
  label: new TranslatableMarkup('Display Links in 1 column'),
  field_types: ['utexas_quick_links'],
  weight: 1
)]
class UTexasQuickLinksDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $item) {
      if (!empty($item->links)) {
        $links = unserialize($item->links, ['allowed_classes' => FALSE]);
        foreach ($links as &$link) {
          if (!empty($link['uri'])) {
            $link_item['link'] = $link;
            $link = UtexasLinkOptionsHelper::buildLink($link_item, ['ut-link']);
          }
        }
      }
      else {
        $links = [];
      }
      $format = $item->copy_format ?? 'flex_html';
      $copy = $item->copy_value ?? '';
      $elements[] = [
        '#theme' => 'utexas_quick_links',
        '#headline' => RenderElementHelper::filterSingleLineText($item->headline, TRUE),
        '#copy' => [
          '#type' => 'processed_text',
          '#text' => $copy,
          '#format' => $format,
          '#langcode' => $item->getLangcode(),
        ],
        '#links' => $links,
      ];
    }
    return $elements;
  }

}
