<?php

namespace Drupal\utexas_resources\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\Traits\UtexasFieldTrait;

/**
 * Plugin implementation of the 'utexas_resources' widget.
 */
#[FieldWidget(
  id: 'utexas_resources',
  label: new TranslatableMarkup('Resources'),
  field_types: ['utexas_resources']
)]
class UTexasResourcesWidget extends WidgetBase {

  use UtexasFieldTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $element['headline'] = [
      '#title' => $this->t('Resource Title'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->headline ?? NULL,
      '#size' => '60',
      '#description' => $this->t('Optionally add a title for these resource collections.'),
      '#maxlength' => 255,
    ];

    // This serialized data is trusted from the component,
    // so we do not restrict object types in unserialize().
    // phpcs:ignore
    $resource_items = !empty($items[$delta]->resource_items) ? unserialize($items[$delta]->resource_items) : [];
    // Ensure item keys are consecutive.
    $resource_items = array_values($resource_items);
    $wrapper_id = $field_name . $delta . 'wrapper';
    $element['#tree'] = TRUE;
    $element['resource_items'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];
    $prepared_items = $this->buildItems($resource_items, $form_state, $field_name, $delta);
    $prepared_items = $this->makeDraggable($prepared_items, $wrapper_id, 'resource_items');
    $element['resource_items'] += $prepared_items;

    $element['resource_actions']['actions'] = [
      '#type' => 'actions',
    ];
    $element['resource_actions']['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another Resource item'),
      '#name' => $field_name . $delta . 'add',
      '#container' => 'resource_items',
      '#action_container' => 'resource_actions',
      '#field_name' => $field_name,
      '#delta' => $delta,
      '#submit' => [[get_class($this), 'subFieldAddAction']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_class($this), 'subFieldAddTarget'],
        'wrapper' => $wrapper_id,
      ],
    ];
    return $element;
  }

  /**
   * Defines the basic form structure for a single item.
   *
   * @param array $item
   *   Single item for this component.
   * @param int $i
   *   The delta for this single item.
   *
   * @return array
   *   The form API structure for this item.
   */
  public function formItemStructure($item, $i) {
    $headline = 'New Resource item';
    if (isset($item['item']['headline'])) {
      $headline = 'Item ' . ($i + 1) . ' (' . $item['item']['headline'] . ')';
    }
    $form = [
      '#type' => 'details',
      '#title' => $this->t('%headline', [
        '%headline' => $headline,
      ]),
    ];
    $form['item'] = [
      '#type' => 'utexas_resource',
      '#default_value' => [
        'headline' => $item['item']['headline'] ?? '',
        'image' => $item['item']['image'] ?? '',
        'links' => $item['item']['links'] ?? FALSE,
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $storage = [];
    // Loop through field deltas. In fields with 1 resource field allowed,
    // there will only be one delta. We nevertheless need to support unlimited
    // cardinality, hence the loop.
    foreach ($values as $delta => $field) {
      if (isset($field['headline'])) {
        // The overall group headline.
        $storage[$delta]['headline'] = $field['headline'];
      }
      if (isset($field['resource_items'])) {
        // Re-sort by the order provided by tabledrag.
        usort($field['resource_items']['items'], function ($item1, $item2) {
          return $item1['weight'] <=> $item2['weight'];
        });
        foreach ($field['resource_items']['items'] as $weight => $item) {
          $elements = $item['details']['item']['item'];
          $storage[$delta]['resource_items'][$weight]['item'] = [];
          if (!empty($elements['headline'])) {
            $storage[$delta]['resource_items'][$weight]['item']['headline'] = $elements['headline'];
          }
          if (!empty($elements['image'])) {
            $storage[$delta]['resource_items'][$weight]['item']['image'] = $elements['image'];
          }
          if (isset($elements['links'])) {
            foreach ($elements['links'] as $link) {
              if (!empty($link['uri']) && !$link['uri'] == '') {
                $storage[$delta]['resource_items'][$weight]['item']['links'][] = $link;
              }
            }
          }
          // Remove empty collections
          // (i.e., user has manually emptied the field contents).
          if (empty($storage[$delta]['resource_items'][$weight]['item'])) {
            unset($storage[$delta]['resource_items'][$weight]);
          }
        }
      }
      // If no Resource collections have been added, remove the empty array.
      if (empty($storage[$delta]['resource_items'])) {
        unset($storage[$delta]['resource_items']);
      }
      else {
        // Resource items are stored in a serialized array,
        // with consecutive keys.
        $storage[$delta]['resource_items'] = serialize(array_values($storage[$delta]['resource_items']));
      }
    }
    return $storage;
  }

}
