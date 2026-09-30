<?php

namespace Drupal\utexas_form_elements\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\linkit\Plugin\Field\FieldWidget\LinkitWidget;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;

/**
 * Plugin implementation of the 'utexas_link_widget' widget.
 */
#[FieldWidget(
  id: 'utexas_link_widget',
  label: new TranslatableMarkup('Link with options'),
  field_types: ['link']
)]
class UtexasLinkWidget extends LinkitWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Get form element as supplied by parent class (LinkWidget).
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Get the form item that this widget is being applied to.
    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];

    // Add link options.
    $link_options_helper = new UtexasLinkOptionsHelper();
    $element = $link_options_helper->addLinkOptions($element, $item);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Check target values for _blank. If _blank is found, add helpful rel
    // values related to preventing "reverse tabnabbing".
    foreach ($values as $delta => $value) {
      if ($value['options']['attributes']['target']['_blank']) {
        $values[$delta]['options']['attributes']['rel'] = [
          'noopener',
          'noreferrer',
        ];
      }
    }

    return $values;
  }

}
