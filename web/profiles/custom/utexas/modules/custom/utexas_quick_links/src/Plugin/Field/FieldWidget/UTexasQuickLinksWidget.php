<?php

namespace Drupal\utexas_quick_links\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\Traits\UtexasFieldTrait;

/**
 * Plugin implementation of the 'utexas_quick_links' widget.
 */
#[FieldWidget(
  id: 'utexas_quick_links',
  label: new TranslatableMarkup('UTexas Quick Links'),
  field_types: ['utexas_quick_links']
)]
class UTexasQuickLinksWidget extends WidgetBase {

  use UtexasFieldTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    $element['headline'] = [
      '#title' => 'Headline',
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->headline ?? '',
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => $items[$delta]->copy_value ?? '',
      '#format' => $items[$delta]->copy_format ?? 'restricted_html',
    ];

    // This serialized data is trusted from the component,
    // so we do not restrict object types in unserialize().
    // phpcs:ignore

    // We have to ensure that there is at least one link field.
    if (isset($items[$delta])) {
      $quick_links_items = isset($items[$delta]->links) ? unserialize($items[$delta]->links, ['allowed_classes' => FALSE]) : [];
    }
    else {
      $quick_links_items = [];
    }

    // Ensure item keys are consecutive.
    $quick_links_items = array_values($quick_links_items);
    $wrapper_id = $field_name . $delta . 'wrapper';

    $element['#tree'] = TRUE;
    $element['quick_links_items'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];
    $prepared_items = $this->buildItems($quick_links_items, $form_state, $field_name, $delta);
    $prepared_items = $this->makeDraggable($prepared_items, $wrapper_id, 'quick_links_items');
    $element['quick_links_items'] += $prepared_items;

    $element['link_actions']['actions']['add'] = [
      '#type' => 'actions',
    ];
    $element['link_actions']['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add item'),
      '#name' => $field_name . $delta . 'add',
      '#container' => 'quick_links_items',
      '#action_container' => 'link_actions',
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
   * Helper function to extract the add more parent element.
   */
  public static function retrieveAddMoreElement($form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = array_slice($triggering_element['#array_parents'], 0, -2);
    return NestedArray::getValue($form, $parents);
  }

  /**
   * Submission handler for the "Add another item" button.
   */
  public static function utexasAddMoreSubmit(array $form, FormStateInterface $form_state) {
    $element = self::retrieveAddMoreElement($form, $form_state);
    array_pop($element['#parents']);
    // The field_delta will be the last (nearest) element in the #parents array.
    $field_delta = array_pop($element['#parents']);
    $field_name = array_pop($element['#parents']);
    // The field_name will be the penultimate element in the #parents array.
    $parents = [$field_name, 'widget'];
    // Increment the items count.
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    $widget_state[$field_name][$field_delta]["counter"]++;
    static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    $form_state
      ->setRebuild();
  }

  /**
   * Callback for ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the links in it.
   */
  public static function utexasAddMoreAjax(array &$form, FormStateInterface $form_state) {
    return self::retrieveAddMoreElement($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through field instances (not link instances).
    foreach ($values as &$value) {
      // Links are stored as a serialized array.
      if (!empty($value['quick_links_items'])) {
        $links_to_store = [];
        // Re-sort by the order provided by tabledrag.
        usort($value['quick_links_items']['items'], function ($item1, $item2) {
          return $item1['weight'] <=> $item2['weight'];
        });
        foreach ($value['quick_links_items']['items'] as $link) {
          $link_data = $link['details']['item']['item'];
          if (!empty($link_data['uri'])) {
            $links_to_store[] = $link_data;
          }
        }
        // Don't serialize an empty array.
        if (!empty($links_to_store)) {
          $value['links'] = serialize($links_to_store);
        }
        else {
          unset($value['links']);
        }
      }
      // Split the "text_format" form element data into our field's schema.
      $value['copy_value'] = $value['copy']['value'];
      $value['copy_format'] = $value['copy']['format'];
    }
    return $values;
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
    $headline = 'New link item';
    if (isset($item['title'])) {
      $headline = 'Item ' . ($i + 1) . ' (' . $item['title'] . ')';
    }
    $form = [
      '#type' => 'details',
      '#title' => $this->t('%headline', [
        '%headline' => $headline,
      ]),

    ];
    $form['item'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $item['uri'] ?? '',
        'title' => $item['title'] ?? '',
        'options' => $item['options'] ?? [],
      ],
    ];
    return $form;
  }

}
