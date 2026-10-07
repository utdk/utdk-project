<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\UtexasWidgetBase;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'utexas_flex_list' widget.
 */
#[FieldWidget(
  id: 'utexas_flex_list',
  label: new TranslatableMarkup('Flex list'),
  field_types: ['utexas_flex_list']
)]
class UTexasFlexList extends UtexasWidgetBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $item = $items[$delta];
    $headline = $item->header ? 'Flex List item ' . ($delta + 1) . ' (' . $item->header . ')' : 'New item';
    $element['utexas_flex_list'] = [
      '#type' => 'details',
      '#title' => $this->t('@headline', ['@headline' => $headline]),
    ];
    $element['utexas_flex_list']['header'] = [
      '#title' => 'Item header',
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->header ?? NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element['utexas_flex_list']['content'] = [
      '#title' => 'Item content',
      '#type' => 'text_format',
      '#default_value' => $items[$delta]->content_value ?? NULL,
      '#format' => $items[$delta]->content_format,
    ];
    $element['#attached']['library'][] = 'utexas_flex_list/widget';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => &$value) {
      // Split the "text_format" form element data into our field's schema.
      $value['header'] = $value['utexas_flex_list']['header'];
      $value['content_value'] = $value['utexas_flex_list']['content']['value'];
      $value['content_format'] = $value['utexas_flex_list']['content']['format'];
      if (empty($value['header']) && !empty($value['content_value'])) {
        $field_name = $this->fieldDefinition->getName();
        $form_state->setError($form[$field_name]['widget'][$delta]['utexas_flex_list']['header'], $this->t('A header must accompany body text.'));
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   *
   * @see Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget.php
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    $element = parent::errorElement($element, $violation, $form, $form_state);
    return ($element === FALSE) ? FALSE : $element[$violation->arrayPropertyPath[0]];
  }

}
