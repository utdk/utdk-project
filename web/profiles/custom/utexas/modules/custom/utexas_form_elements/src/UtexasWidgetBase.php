<?php

namespace Drupal\utexas_form_elements;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Base class for 'Field widget' plugin implementations.
 *
 * @ingroup field_widget
 */
class UtexasWidgetBase extends WidgetBase implements WidgetInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
  }

  /**
   * Special handling to create form elements for multiple values.
   *
   * Handles generic features for multiple fields:
   * - number of widgets
   * - AHAH-'add more' button
   * - table display and drag-n-drop value reordering.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $label = $this->fieldDefinition->getLabel();
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $parents = $form['#parents'];
    $id_prefix = implode('-', array_merge($parents, [$field_name]));
    $wrapper_id = Html::getUniqueId($id_prefix . '-add-more-wrapper');

    // Get a list of fields that are marked to be removed.
    $items_to_remove = $form_state->get('items_to_remove');
    // If no fields have been removed yet we use an empty array.
    if ($items_to_remove === NULL) {
      $form_state->set('items_to_remove', []);
      $items_to_remove = $form_state->get('items_to_remove');
    }

    // Determine the number of widgets to display.
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED:
        $field_state = static::getWidgetState($parents, $field_name, $form_state);
        $max = $field_state['items_count'];
        $is_multiple = TRUE;
        break;

      default:
        $max = $cardinality - 1;
        $is_multiple = ($cardinality > 1);
        break;
    }

    $title = $this->fieldDefinition->getLabel();
    $description = $this->getFilteredDescription();

    $elements = [];

    for ($delta = 0; $delta <= $max; $delta++) {
      // Added for 'Remove' functionality.
      if (in_array($delta, $items_to_remove)) {
        continue;
      }
      // Add a new empty item if it doesn't exist yet at this delta.
      if (!isset($items[$delta])) {
        $items->appendItem();
      }

      // For multiple fields, title and description are handled by the wrapping
      // table.
      if ($is_multiple) {
        $element = [
          '#title' => $this->t('@title (value @number)', [
            '@title' => $title,
            '@number' => $delta + 1,
          ]),
          '#title_display' => 'invisible',
          '#description' => '',
        ];
      }
      else {
        $element = [
          '#title' => $title,
          '#title_display' => 'before',
          '#description' => $description,
        ];
      }

      $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

      $element = $this->populateItem($items, $delta, $element, $form, $form_state, $is_multiple, $max, $field_name, $wrapper_id);
      if ($element) {
        $elements[$delta] = $element;
      }
    }
    if (empty($elements) && $is_multiple) {
      // Ensure at least one element.
      $delta = $delta + 1;
      $element = [
        '#title' => $this->t('@title (value @number)', [
          '@title' => $title,
          '@number' => $delta + 1,
        ]),
        '#title_display' => 'invisible',
        '#description' => '',
      ];
      $element = $this->populateItem($items, $delta, $element, $form, $form_state, $is_multiple, $max, $field_name, $wrapper_id);
      $elements[$delta] = $element;
    }

    if ($elements) {
      $elements += [
        '#theme' => 'field_multiple_value_form',
        '#field_name' => $field_name,
        '#cardinality' => $cardinality,
        '#cardinality_multiple' => $this->fieldDefinition->getFieldStorageDefinition()->isMultiple(),
        '#required' => $this->fieldDefinition->isRequired(),
        '#title' => $title,
        '#description' => $description,
        '#max_delta' => $max,
      ];

      // Add 'add more' button, if not working with a programmed form.
      if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && !$form_state->isProgrammed()) {
        $elements['#prefix'] = '<div id="' . $wrapper_id . '">';
        $elements['#suffix'] = '</div>';

        $elements['add_more'] = [
          '#type' => 'submit',
          '#name' => strtr($id_prefix, '-', '_') . '_add_more',
          '#value' => $this->t('Add another @label item', [
            '@label' => $label,
          ]),
          '#attributes' => ['class' => ['field-add-more-submit']],
          '#limit_validation_errors' => [array_merge($parents, [$field_name])],
          '#submit' => [[static::class, 'addMoreSubmit']],
          '#ajax' => [
            'callback' => [static::class, 'addMoreAjax'],
            'wrapper' => $wrapper_id,
            'effect' => 'fade',
          ],
        ];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function populateItem($items, $delta, $element, $form, $form_state, $is_multiple, $max, $field_name, $wrapper_id) {
    $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

    if ($element) {
      // Input field for the delta (drag-n-drop reordering).
      if ($is_multiple) {
        // We name the element '_weight' to avoid clashing with elements
        // defined by widget.
        $element['_weight'] = [
          '#type' => 'weight',
          '#title' => $this->t('Weight for row @number', ['@number' => $delta + 1]),
          '#title_display' => 'invisible',
          // Note: this 'delta' is the FAPI #type 'weight' element's property.
          '#delta' => $max,
          '#default_value' => $items[$delta]->_weight ?: $delta,
          '#weight' => 100,
        ];
        $offset = $delta + 1;
        $headline = 'item ' . $offset;
        $element['remove'] = [
          '#type' => 'submit',
          '#value' => $this->t('Remove %headline?', [
            '%headline' => $headline,
          ]),
          '#name' => $field_name . $delta,
          '#field_name' => $field_name,
          '#delta' => $delta,
          '#submit' => [[get_class($this), 'fieldRemoveAction']],
          '#limit_validation_errors' => [],
          '#ajax' => [
            'callback' => [get_class($this), 'fieldRemoveTarget'],
            'wrapper' => $wrapper_id,
            'event' => 'click',
          ],
          '#attributes' => [
            'class' => [
              'visually-hidden',
            ],
          ],
          '#weight' => 101,
        ];
        $element['confirm-remove'] = [
          '#type' => 'submit',
          '#submit' => [],
          '#value' => $this->t('Remove %headline', [
            '%headline' => $headline,
          ]),
          '#button_type' => 'secondary',
          '#attributes' => [
            'class' => [
              'confirm-remove',
            ],
            'data-remove-target' => $field_name . $delta,
          ],
          '#attached' => [
            'library' => [
              'utexas_form_elements/confirm-remove',
            ],
          ],
          '#weight' => 102,
        ];
      }
    }
    return $element;
  }

  /**
   * Callback for 'Remove item' button.
   *
   * Selects and returns the array depth with the component items in it.
   */
  public static function fieldRemoveTarget(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $field = $triggering_element['#field_name'];
    // Traverse parents until we get to the target field.
    // Necessary because the inline and reusable blocks parents differ.
    foreach ($triggering_element['#parents'] as $parent) {
      $parents[] = $parent;
      if ($parent === $field) {
        break;
      }
    }
    if (!in_array($field, $parents)) {
      return [];
    }
    $target = NestedArray::getValue($form, $parents);
    return $target;
  }

  /**
   * Submit handler for the "remove" button.
   *
   * Removes the corresponding line.
   */
  public static function fieldRemoveAction(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $index_to_remove = $triggering_element['#delta'];
    // Keep track of removed fields so we can add new fields at the bottom
    // Without this they would be added where a value was removed.
    $items_to_remove = $form_state->get('items_to_remove');
    $items_to_remove[] = $index_to_remove;
    $form_state->set('items_to_remove', $items_to_remove);
    $parents = [];
    // Traverse parents until we get to `promo_unit_items`.
    // Necessary because the inline and reusable blocks parents differ.
    foreach ($triggering_element['#parents'] as $parent) {
      if ($parent === 'widget') {
        // Form inputs do not include this parent.
        continue;
      }
      if ($parent === 'remove') {
        // Form inputs do not include this parent.
        continue;
      }
      $parents[] = $parent;
    }
    if (!in_array($triggering_element['#field_name'], $parents)) {
      return [];
    }
    // $parents should now look like this:
    // ['field', 'widget', 'fielddelta']
    $to_remove = NestedArray::getValue($form, $parents);
    // Remove the fieldset from $form (the easy way)
    unset($to_remove);
    // Remove the fieldset from $form_state (the hard way)
    // First fetch the fieldset, then edit it, then set it again
    // Form API does not allow us to directly edit the field.
    $values = $form_state->getValues();
    $inputs = $form_state->getUserInput();
    NestedArray::setValue($values, $parents, [], TRUE);
    NestedArray::setValue($inputs, $parents, [], TRUE);
    // Important: set the user input to the new manipulated state so that
    // default values populate correctly.
    $form_state->setValues($values);
    $form_state->setUserInput($inputs);

    // Rebuild form_state.
    $form_state->setRebuild();
  }

}
