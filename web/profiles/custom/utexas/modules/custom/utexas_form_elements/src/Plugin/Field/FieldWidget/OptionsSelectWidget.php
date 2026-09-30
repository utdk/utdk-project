<?php

namespace Drupal\utexas_form_elements\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget as BaseOptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\Traits\TaxonomyDescriptionTrait;

/**
 * Alters the default 'options_select' widget.
 */
#[FieldWidget(
  id: 'options_select',
  label: new TranslatableMarkup('Select list'),
  field_types: [
    'entity_reference',
    'list_integer',
    'list_float',
    'list_string',
  ],
  multiple_values: TRUE
)]
class OptionsSelectWidget extends BaseOptionsSelectWidget {

  use TaxonomyDescriptionTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $type = $items->getFieldDefinition()->getType();
    $settings = $items->getFieldDefinition()->getSettings();
    if ($type == 'entity_reference' && $settings['target_type'] == 'taxonomy_term') {
      $element = $this->addDynamicTaxonomyDescription($element, $items);
    }
    return $element;
  }

}
