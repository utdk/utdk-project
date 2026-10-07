<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\Traits\UtexasFieldTrait;

/**
 * Plugin implementation of the 'utexas_promo_list' widget.
 */
#[FieldWidget(
  id: 'utexas_promo_list',
  label: new TranslatableMarkup('Promo List'),
  field_types: ['utexas_promo_list']
)]
class UTexasPromoListWidget extends WidgetBase {

  use UtexasFieldTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $element['headline'] = [
      '#title' => 'List Headline',
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->headline ?? NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    // Gather the number of items in the Promo List.
    // This serialized data is trusted from the component,
    // so we do not restrict object types in unserialize().
    // phpcs:ignore
    $items = !empty($items[$delta]->promo_list_items) ? unserialize($items[$delta]->promo_list_items) : [];
    // Ensure item keys are consecutive.
    $items = array_values($items);

    $wrapper_id = $field_name . $delta . 'wrapper';
    $element['#tree'] = TRUE;
    $element['promo_list_items'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];
    $prepared_items = $this->buildItems($items, $form_state, $field_name, $delta);
    $prepared_items = $this->makeDraggable($prepared_items, $wrapper_id, 'promo_list_items');
    $element['promo_list_items'] += $prepared_items;

    $element['promo_list_actions']['actions'] = [
      '#type' => 'actions',
    ];
    $element['promo_list_actions']['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Promo List item'),
      '#container' => 'promo_list_items',
      '#action_container' => 'promo_list_actions',
      '#name' => $field_name . $delta . 'add',
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
    $headline = 'New Promo List item';
    if (isset($item['item']['headline'])) {
      $headline = 'Item ' . ($i + 1) . '(' . $item['item']['headline'] . ')';
    }
    $form = [
      '#type' => 'details',
      '#title' => $this->t('%headline', [
        '%headline' => $headline,
      ]),
    ];
    $form['item'] = [
      '#type' => 'utexas_promo_list',
      '#default_value' => [
        'headline' => $item['item']['headline'] ?? '',
        'image' => $item['item']['image'] ?? '',
        'copy_value' => $item['item']['copy']['value'] ?? '',
        'copy_format' => $item['item']['copy']['format'] ?? 'restricted_html',
        'link' => $item['item']['link'] ?? '',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $storage = [];
    // Loop through field deltas.
    foreach ($values as $delta => $field) {
      if (isset($field['headline'])) {
        // The overall group headline.
        $storage[$delta]['headline'] = $field['headline'];
      }
      if (isset($field['promo_list_items'])) {
        // Re-sort by the order provided by tabledrag.
        usort($field['promo_list_items']['items'], function ($item1, $item2) {
          return $item1['weight'] <=> $item2['weight'];
        });
        foreach ($field['promo_list_items']['items'] as $weight => $item) {
          $elements = $item['details']['item']['item'];
          $storage[$delta]['promo_list_items'][$weight]['item'] = [];
          if (!empty($elements['headline'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['headline'] = $elements['headline'];
          }
          if (!empty($elements['image'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['image'] = $elements['image'];
          }
          if (!empty($elements['copy']['value'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['copy'] = $elements['copy'];
          }
          if (!empty($elements['link']['uri'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['link']['uri'] = $elements['link']['uri'];
            $storage[$delta]['promo_list_items'][$weight]['item']['link']['title'] = $elements['link']['title'];
            $storage[$delta]['promo_list_items'][$weight]['item']['link']['options'] = $elements['link']['options'];
          }
          // Remove empty items
          // (i.e., user has manually emptied the field contents).
          if (empty($storage[$delta]['promo_list_items'][$weight]['item'])) {
            unset($storage[$delta]['promo_list_items'][$weight]);
          }
        }
      }
      // If no Promo List items have been added, remove the empty array.
      if (empty($storage[$delta]['promo_list_items'])) {
        unset($storage[$delta]['promo_list_items']);
      }
      else {
        // Promo List items are stored in a serialized array,
        // with consecutive keys.
        $storage[$delta]['promo_list_items'] = serialize(array_values($storage[$delta]['promo_list_items']));
      }
    }
    return $storage;
  }

}
