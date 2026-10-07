<?php

namespace Drupal\utexas_promo_list\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Defines an element for a single link + title field.
 */
#[FormElement('utexas_promo_list')]
class UTexasPromoListElement extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => [
        [$class, 'processLinkElement'],
      ],
    ];
  }

  /**
   * Process handler for the link form element.
   */
  public static function processLinkElement(&$element, FormStateInterface $form_state, &$form) {
    $element['headline'] = [
      '#type' => 'textfield',
      '#title' => t('Item Headline'),
      '#default_value' => $element['#default_value']['headline'] ?? '',
      '#description' => t('To make this headline into a hyperlink, enter a URL in the field below.'),
    ];
    $image_default = $element['#default_value']['image'] ?? 0;
    if (is_array($image_default)) {
      $image_default = $image_default[0];
    }
    $element['image'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => MediaEntityImageHelper::getAllowedBundles(),
      '#cardinality' => 1,
      '#name' => 'image',
      '#title' => t('Image'),
      '#default_value' => MediaEntityImageHelper::checkMediaExists($image_default),
      '#description' => t('Image will be scaled and cropped to a 1:1 ratio. Ideally, upload an image of 170x170 pixels to maintain resolution & avoid cropping.'),
      '#upload_location' => 'public://promo_list_items/',
    ];
    $element['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => $element['#default_value']['copy_value'] ?? NULL,
      '#format' => $element['#default_value']['copy_format'] ?? 'restricted_html',
    ];
    $element['link'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $element['#default_value']['link']['uri'] ?? NULL,
        'title' => $element['#default_value']['link']['title'] ?? NULL,
        'options' => $element['#default_value']['link']['options'] ?? [],
      ],
      '#suppress_title_display' => TRUE,
    ];
    $element['link']['#description'] = t('A valid URL for this promo list. If present, the item headline and image will become links. Start typing the title of a piece of content to select it. You can also enter an internal path such as %internal or an external URL such as %external. Enter %front to link to the front page.', [
      '%internal' => '/node/add',
      '%external' => 'https://example.com',
      '%front' => '<front>',
    ]);
    return $element;
  }

}
