<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\UtexasWidgetBase;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Plugin implementation of the 'utexas_hero' widget.
 */
#[FieldWidget(
  id: 'utexas_hero',
  label: new TranslatableMarkup('Hero'),
  field_types: ['utexas_hero']
)]
class UTexasHeroWidget extends UtexasWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    // Get the form item that this widget is being applied to.
    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];
    $element['media'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => MediaEntityImageHelper::getAllowedBundles(),
      '#delta' => $delta,
      '#cardinality' => 1,
      '#title' => $this->t('Image'),
      '#default_value' => MediaEntityImageHelper::checkMediaExists($item->media),
      '#description' => $this->t('Upload an image with a minimum resolution of 2280px X 1232px (a 1.85:1 ratio) for the Default style or Hero style 4. Upload an image with a minimum resolution of 2250px X 900px (a 2.5:1 ratio) for Style 1, Style 2, Style 3 and Style 5, which also have a displayed height limitation of 543px.'),
    ];
    $element['disable_image_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable image size optimization.'),
      '#description' => $this->t('Check this if you need to display an animated GIF or have specific image dimensions requirements.'),
      '#default_value' => $item->disable_image_styles ?? 0,
      '#states' => [
        'invisible' => [
          ':input[name="' . $field_name . '[' . $delta . '][media][media_library_selection]"]' => ['value' => "0"],
        ],
      ],
    ];
    $element['selector_target'] = [
      '#prefix' => '<div id="hero_selector_target"></div>',
    ];
    $element['heading'] = [
      '#title' => $this->t('Heading'),
      '#type' => 'textfield',
      '#default_value' => $item->heading ?? NULL,
      '#size' => '60',
      '#description' => $this->t('Optional, but recommended to provide alternative textual explanation of the media.'),
      '#maxlength' => 255,
    ];
    $element['subheading'] = [
      '#title' => $this->t('Subheading'),
      '#type' => 'textfield',
      '#default_value' => $item->subheading ?? NULL,
      '#size' => '60',
      '#description' => $this->t('Optional. Displays directly beneath the heading. For best appearance, use no more than 140 characters. Note: this field is not visible in the default display or in hero style 2.'),
      '#maxlength' => 255,
    ];
    $element['caption'] = [
      '#title' => $this->t('Caption'),
      '#type' => 'textfield',
      '#default_value' => isset($item->subheading) ? $item->caption : NULL,
      '#size' => '60',
      '#description' => $this->t('Optional text to display directly beneath the media.'),
      '#maxlength' => 255,
    ];
    $element['credit'] = [
      '#title' => $this->t('Credit'),
      '#type' => 'textfield',
      '#default_value' => isset($item->subheading) ? $item->credit : NULL,
      '#size' => '60',
      '#description' => $this->t('Optional way to provide attribution, displayed directly beneath the media.'),
      '#maxlength' => 255,
    ];
    $element['cta'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Call to Action'),
    ];
    $element['cta']['link'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $item->link_uri ?? '',
        'title' => $item->link_title ?? '',
        'options' => $item->link_options ?? [],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through (potential) field instances.
    foreach ($values as &$value) {
      if (empty($value['media'])) {
        // A null media value should be saved as 0.
        $value['media'] = 0;
      }
      if (isset($value['cta']['link']['uri'])) {
        $value['link_uri'] = $value['cta']['link']['uri'] ?? '';
        $value['link_title'] = $value['cta']['link']['title'] ?? '';
        $value['link_options'] = $value['cta']['link']['options'] ?? [];
      }
    }
    return $values;
  }

}
