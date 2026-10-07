<?php

namespace Drupal\utexas_form_elements\Traits;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * General-purpose method for modifying custom compound fields.
 */
trait UtexasFieldTrait {

  /**
   * Prepare items, accounting for add/remove buttons.
   *
   * @param array $items
   *   Any items in the database.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The current user input of the form.
   * @param string $field_name
   *   The machine name of the field associated with this instance.
   * @param int $delta
   *   The field instance (not the item instance).
   *
   * @return array
   *   A render array of a draggable table of items.
   */
  public function buildItems(array $items, FormStateInterface $form_state, $field_name, $delta) {
    $add_holder = $field_name . $delta . 'counter';
    $remove_holder = $field_name . $delta . 'wrapper_items_to_remove';
    $counter = $form_state->get($add_holder);
    // Gather the number of items in the form already.
    if ($counter === NULL) {
      // No item count has been set yet by the user. Count the actual items.
      $count = count($items);
      if ($count === 0) {
        $count = 1;
      }
      $form_state->set($add_holder, $count);
      $counter = $form_state->get($add_holder);
    }
    // Get a list of fields that are marked to be removed.
    $items_to_remove = $form_state->get($remove_holder);
    // If no fields have been removed yet we use an empty array.
    if ($items_to_remove === NULL) {
      $form_state->set($remove_holder, []);
      $items_to_remove = $form_state->get($remove_holder);
    }
    $form = [
      'items' => [],
    ];
    for ($i = 0; $i < $counter; $i++) {
      // Check if field was removed.
      if (in_array($i, $items_to_remove)) {
        // Skip if field was removed and move to the next field.
        continue;
      }
      $item = $items[$i] ?? NULL;
      $form['items'][$i] = $this->formItemStructure($item, $i);
    }
    if (empty($form['items'])) {
      // Ensure at least one item (edge case: someone removes the only
      // remaining item). The NULL value will ensure default is empty.
      $form['items'][] = $this->formItemStructure(NULL, NULL);
    }
    return $form;
  }

  /**
   * Create a tabledrag container for all items.
   *
   * @param array $items
   *   The built items.
   * @param string $wrapper
   *   The HTML ID used for AJAX targeting.
   * @param string $container
   *   The element name that wraps the items.
   *
   * @return array
   *   A render array of a draggable table of items.
   */
  public function makeDraggable(array $items, $wrapper, $container) {
    $group_class = 'group-order-weight';
    if (isset($items['items'])) {
      $items = $items['items'];
    }
    // Build table.
    $form = [];
    $form['items'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Order'),
        $this->t('Items'),
        $this->t('Weight'),
        $this->t('Remove'),
      ],
      '#empty' => $this->t('No items.'),
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
    ];

    // Match Drupal core 'show weights' behavior.
    $weight = ceil(count($items) / 2) * -1;

    // Build rows.
    foreach ($items as $inc => $item) {
      $inc = (int) $inc;
      $form_items[$inc] = [];
      $form_items[$inc]['#attributes']['class'][] = 'draggable';
      $form_items[$inc]['#attributes']['class'][] = 'utexas-draggable';
      $form_items[$inc]['#weight'] = $weight;
      $form_items[$inc]['drag'] = [];
      // Let the form take care of populating default values from user input.
      $form_items[$inc]['details']['item'] = $item;
      // Weight column.
      $form_items[$inc]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for item @key', ['@key' => $weight]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#delta' => ceil(count($items) / 2),
        '#attributes' => ['class' => [$group_class]],
      ];
      $offset = $inc + 1;
      $headline = 'item ' . $offset;
      $form_items[$inc]['actions']['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove %headline?', [
          '%headline' => $headline,
        ]),
        '#container' => $container,
        '#wrapper' => $wrapper,
        '#name' => $wrapper . $inc,
        '#delta' => $inc,
        '#submit' => [[get_class($this), 'subFieldRemoveAction']],
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => [get_class($this), 'subFieldRemoveTarget'],
          'wrapper' => $wrapper,
          'event' => 'click',
        ],
        '#attributes' => [
          'class' => [
            'visually-hidden',
          ],
        ],
      ];
      $form_items[$inc]['actions']['confirm-remove'] = [
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
          'data-remove-target' => $wrapper . $inc,
        ],
        '#attached' => [
          'library' => [
            'utexas_form_elements/confirm-remove',
            'utexas_form_elements/draggable-table',
          ],
        ],
      ];
      $inc++;
      $weight++;
    }
    $form['items'] += $form_items;
    return $form;
  }

  /**
   * Submit handler for the "Add another" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public static function subFieldAddAction(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $delta = $triggering_element['#delta'];
    $field_name = $triggering_element['#field_name'];
    $holder = $field_name . $delta . 'counter';
    $num_field = $form_state->get($holder);
    $add_button = $num_field + 1;
    $form_state->set($holder, $add_button);
    $form_state->setRebuild();
  }

  /**
   * Callback for 'Add item' button.
   *
   * Selects and returns the array depth with the component items in it.
   */
  public static function subFieldAddTarget(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $container = $triggering_element['#container'];
    $action_container = $triggering_element['#action_container'];
    $parents = [];
    // Traverse parents until we get to `resource_items`. This is needed
    // because the structure of an Inline block differs from Reusable.
    foreach ($triggering_element['#array_parents'] as $parent) {
      if ($parent !== $action_container) {
        $parents[] = $parent;
      }
      if ($parent === $action_container) {
        $parents[] = $container;
        break;
      }
    }
    if (!in_array($container, $parents)) {
      return [];
    }
    $target = NestedArray::getValue($form, $parents);
    return $target;
  }

  /**
   * Callback for 'Remove item' button.
   *
   * Selects and returns the array depth with the component items in it.
   */
  public static function subfieldRemoveTarget(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $container = $triggering_element['#container'];
    $parents = [];
    // Traverse parents until we get to `resource_items`. This is needed
    // because the structure of an Inline block differs from Reusable.
    foreach ($triggering_element['#array_parents'] as $parent) {
      if ($parent !== $container) {
        $parents[] = $parent;
      }
      if ($parent === $container) {
        $parents[] = $container;
        break;
      }
    }
    if (!in_array($container, $parents)) {
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
  public static function subFieldRemoveAction(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $index_to_remove = $triggering_element['#delta'];
    $container = $triggering_element['#container'];
    $wrapper = $triggering_element['#wrapper'];
    $remove_holder = $wrapper . '_items_to_remove';
    // Keep track of removed fields so we can add new fields at the bottom
    // Without this they would be added where a value was removed.
    $items_to_remove = $form_state->get($remove_holder);
    $items_to_remove[] = $index_to_remove;
    $form_state->set($remove_holder, $items_to_remove);
    $parents = [];
    // Traverse parents until we get to $container.
    // Necessary because the inline and reusable blocks parents differ.
    foreach ($triggering_element['#array_parents'] as $parent) {
      if ($parent === 'widget') {
        // Form inputs do not include this parent.
        continue;
      }
      if ($parent !== $container) {
        $parents[] = $parent;
      }
      if ($parent === $container) {
        $parents[] = $container;
        break;
      }
    }
    if (!in_array($container, $parents)) {
      return [];
    }

    $parents[] = 'items';
    $parents[] = $index_to_remove;
    // $parents should now look like this:
    // ['field', 'fielddelta', 'container', 'items', 'instancedelta']
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
