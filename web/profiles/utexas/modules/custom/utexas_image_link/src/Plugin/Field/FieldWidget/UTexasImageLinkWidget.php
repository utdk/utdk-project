<?php

namespace Drupal\utexas_image_link\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Plugin implementation of the 'utexas_image_link' widget.
 */
#[FieldWidget(
  id: 'utexas_image_link',
  label: new TranslatableMarkup('UTexas Image Link'),
  field_types: ['utexas_image_link']
)]
class UTexasImageLinkWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Get the form item that this widget is being applied to.
    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];

    $element['image'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => MediaEntityImageHelper::getAllowedBundles(),
      '#delta' => $delta,
      '#cardinality' => 1,
      '#title' => $this->t('Image'),
      '#default_value' => MediaEntityImageHelper::checkMediaExists($item->image),
      '#description' => $this->t('This image will fill the width of the region it is placed in.'),
      '#required' => TRUE,
    ];
    $element['link'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $item->link ?? NULL,
        'title' => $item->link_text ?? NULL,
        'options' => $item->link_options ?? [],
      ],
      '#description' => $this->t('Use the "Link text" field to provide wording, visible only to screen readers, that describes the link destination.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through (potential) field instances.
    foreach ($values as &$value) {
      // A null media value should be saved as 0.
      if (empty($value['image'])) {
        $value['image'] = 0;
      }
      // We only want the 'uri' part of the link for image link, but for
      // consistency we leave the code here to store all link values.
      $value['link_text'] = $value['link']['title'] ?? NULL;
      $value['link_options'] = $value['link']['options'] ?? NULL;
      // Since the storage value is 'link', we must assign its value last.
      $value['link'] = $value['link']['uri'] ?? NULL;
    }

    return $values;
  }

}
