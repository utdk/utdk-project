<?php

namespace Drupal\utexas_form_elements\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget as BaseAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\Traits\TaxonomyDescriptionTrait;

/**
 * Alters the default 'entity_reference_autocomplete' widget.
 */
#[FieldWidget(
  id: 'entity_reference_autocomplete',
  label: new TranslatableMarkup('Autocomplete'),
  description: new TranslatableMarkup('An autocomplete text field.'),
  field_types: ['entity_reference']
)]
class EntityReferenceAutocompleteWidget extends BaseAutocompleteWidget {

  use TaxonomyDescriptionTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $type = $items->getFieldDefinition()->getType();
    $settings = $items->getFieldDefinition()->getSettings();
    if ($type == 'entity_reference' && $settings['target_type'] == 'taxonomy_term') {
      $element['target_id'] = $this->addDynamicTaxonomyDescription($element['target_id'], $items);
    }
    return $element;
  }

}
