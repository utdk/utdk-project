<?php

namespace Drupal\utexas_block_social_links\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\utexas_block_social_links\Services\UTexasSocialLinkOptions;
use Drupal\utexas_form_elements\RenderElementHelper;

/**
 * Plugin implementation of the 'utexas_social_link_formatter' formatter.
 */
#[FieldFormatter(
  id: 'utexas_social_link_formatter',
  label: new TranslatableMarkup('UTexas Social Link'),
  field_types: ['utexas_social_link_field']
)]
class UTexasSocialLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $icons = UTexasSocialLinkOptions::getIcons();
    // Add CSS derived from site-specific social media icons.
    $inline_css = '';
    foreach ($icons as $name => $icon) {
      if (file_exists($icon)) {
        // Bypass code syntax expectation for dependency injection.
        // phpcs:ignore
        $absolute_filepath = \Drupal::service('file_url_generator')->generateAbsoluteString($icon);
        // phpcs:ignore
        $relative_filepath = \Drupal::service('file_url_generator')->transformRelative($absolute_filepath);
        $inline_css .= ".block__ut-social-links--link." . $name . " {
          mask: url('" . $relative_filepath . "');mask-size: cover;}";
      }
    }
    $elements['#attached']['html_head'][] = [
      [
        '#tag' => 'style',
        '#value' => $inline_css,
      ],
      Html::getUniqueId('utexas-block-social-links'),
    ];
    // Default to small for backwards compatibility.
    $icon_size = $items[0]->icon_size ?? 'ut-social-links--small';
    $elements['#icon_size'] = $icon_size;
    foreach ($items as $delta => $item) {
      if ($item->social_account_links) {
        $stored_links = $item->social_account_links ?? '';
        // Bypass requirement to specify allowed classes since they are unknown.
        // phpcs:ignore
        $social_account_links = (array) unserialize($stored_links, ['allowed_classes' => TRUE]);
        foreach ($social_account_links as $key => $val) {
          $name = $val['social_account_name'];
          if (!file_exists($icons[$name])) {
            // phpcs:ignore
            \Drupal::logger('utexas_block_social_links')->warning('The icon for %social is missing. Update it on the <a href="/admin/structure/social-links">social links configuration page</a>.', [
              '%social' => $name,
            ]);
            continue;
          }
          $label = "Find us on " . ucfirst($name);
          $link_text = Markup::create("<span class='visually-hidden'>$label</span>");
          $linked_icon_options = [
            'attributes' => [
              'class' => [
                'block__ut-social-links--link',
                $name,
              ],
            ],
          ];
          $linked_icon = Link::fromTextAndUrl($link_text, Url::fromUri($val['social_account_url'], $linked_icon_options));
          $renderable = $linked_icon->toRenderable();
          $elements[$delta]['links'][$key] = $renderable;
        }
      }
      // Add class to the item.attributes object.
      $elements['#items'][$delta] = new \stdClass();
      $elements['#items'][$delta]->_attributes['class'][] = 'block__ut-social-links--item';

      if ($item->headline) {
        $elements[$delta]['headline'] = RenderElementHelper::filterSingleLineText($item->headline, TRUE);
      }
    }
    $elements['#attached']['library'][] = 'utexas_block_social_links/display';
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);
    $elements['#cache']['tags'][] = 'utexas_social_links_block';
    return $elements;
  }

}
