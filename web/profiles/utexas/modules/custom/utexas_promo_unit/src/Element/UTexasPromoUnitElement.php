<?php

namespace Drupal\utexas_promo_unit\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Defines an element with image, headline, copy and single link.
 */
#[FormElement('utexas_promo_unit')]
class UTexasPromoUnitElement extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => [
        [$class, 'processPromoUnitElement'],
      ],
    ];
  }

  /**
   * Process handler for the link form element.
   */
  public static function processPromoUnitElement(&$element, FormStateInterface $form_state, &$form) {
    $element['headline'] = [
      '#type' => 'textfield',
      '#title' => t('Item Headline'),
      '#default_value' => $element['#default_value']['headline'] ?? '',
      '#description' => t('To make this headline into a hyperlink, enter a URL in the field below.'),
    ];
    $image_default = $element['#default_value']['image'] ?? 0;
    if (is_array($image_default)) {
      $image_default = reset($image_default);
    }
    $element['image'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => MediaEntityImageHelper::getAllowedBundles(),
      '#cardinality' => 1,
      '#name' => 'image',
      '#title' => t('Image'),
      '#default_value' => MediaEntityImageHelper::checkMediaExists($image_default),
      '#description' => t('Upload an image with aspect ratio appropriate to the view mode you select (see below). To accommodate high resolution screens, the image widths should be at least 1600 pixels.'),
      '#upload_location' => 'public://promo_unit_items/',
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
        'uri' => $element['#default_value']['link']['uri'] ?? '',
        'title' => $element['#default_value']['link']['title'] ?? '',
        'options' => $element['#default_value']['link']['options'] ?? [],
      ],
      '#title_description' => "Optional. Leave blank to link only the item headline. Add text to print a second link at the bottom of the item.",
    ];
    return $element;
  }

}
