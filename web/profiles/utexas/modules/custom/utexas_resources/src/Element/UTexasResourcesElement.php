<?php

namespace Drupal\utexas_resources\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Defines an element with image, headline, and unlimited links.
 */
#[FormElement('utexas_resource')]
class UTexasResourcesElement extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => [
        [$class, 'processResourceElement'],
      ],
    ];
  }

  /**
   * Process handler for the form element.
   */
  public static function processResourceElement(&$element, FormStateInterface $form_state, &$form) {
    $element['headline'] = [
      '#type' => 'textfield',
      '#title' => t('Headline'),
      '#default_value' => $element['#default_value']['headline'] ?? '',
    ];
    $image_default = $element['#default_value']['image'] ?? 0;
    if (is_array($image_default)) {
      $image_default = reset($image_default);
    }
    $element['image'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => MediaEntityImageHelper::getAllowedBundles(),
      '#cardinality' => 1,
      '#name' => 'image',
      '#title' => t('Image'),
      '#default_value' => MediaEntityImageHelper::checkMediaExists($image_default),
      '#description' => t('Image will be automatically cropped to 400 x 250. Upload an image with an aspect ratio equal to 400 x 250 to avoid cropping.'),
    ];
    $agnostic_parents = self::makeParentsAgnostic($element['#parents']);
    $field_name = $agnostic_parents['field_machine_name'];
    $field_delta = $agnostic_parents['field_delta'];
    $resource_delta = $agnostic_parents['item_delta'];
    $parents = [$field_name];
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    // This value is defined/leveraged by ::utexasAddMoreSubmit().
    $link_count = $widget_state[$field_name][$field_delta][$resource_delta]["counter"] ?? NULL;

    // We have to ensure that there is at least one link field.
    if (isset($element['#default_value']['links'])) {
      $links = $element['#default_value']['links'];
    }
    if ($link_count === NULL) {
      if (empty($links)) {
        $link_count = 1;
      }
      else {
        $link_count = count($links);
      }
      $widget_state[$field_name][$field_delta][$resource_delta]["counter"] = $link_count;
      static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    }
    $wrapper_id = Html::getUniqueId('ajax-wrapper');
    $element['links'] = [
      '#type' => 'fieldset',
      '#title' => t('List of links'),
    ];
    $element['links']['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['links']['#suffix'] = '</div>';
    for ($i = 0; $i < $link_count; $i++) {
      $element['links'][$i] = [
        '#type' => 'utexas_link_options_element_multiple',
        '#default_value' => [
          'uri' => $links[$i]['uri'] ?? NULL,
          'title' => $links[$i]['title'] ?? NULL,
          'options' => $links[$i]['options'] ?? [],
        ],
      ];
    }
    // We limit form validation so that other elements are not validated
    // during this submit button's refresh action. See
    // See https://www.drupal.org/project/drupal/issues/2476569
    $element['links']['actions']['add_link'] = [
      '#type' => 'submit',
      '#name' => $field_name . $field_delta . $resource_delta,
      '#value' => t('Add link'),
      '#submit' => ['\Drupal\utexas_resources\Element\UTexasResourcesElement::utexasAddMoreSubmit'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_called_class(), 'utexasAddMoreAjax'],
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
    $agnostic_parents = self::makeParentsAgnostic($element['#parents']);
    // The field_delta will be the last (nearest) element in the #parents array.
    $field_name = $agnostic_parents['field_machine_name'];
    $field_delta = $agnostic_parents['field_delta'];
    $resource_delta = $agnostic_parents['item_delta'];

    // The field_name will be the penultimate element in the #parents array.
    $parents = [$field_name];
    // Increment the items count.
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    $widget_state[$field_name][$field_delta][$resource_delta]["counter"]++;
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
  public static function getWidgetState(array $parents, $field_name, FormStateInterface $form_state) {
    $widget_state = NestedArray::getValue($form_state->getStorage(), static::getWidgetStateParents($parents, $field_name));
    return $widget_state;
  }

  /**
   * {@inheritdoc}
   */
  public static function setWidgetState(array $parents, $field_name, FormStateInterface $form_state, array $field_state) {
    NestedArray::setValue($form_state->getStorage(), static::getWidgetStateParents($parents, $field_name), $field_state);
  }

  /**
   * Returns the location of processing information within $form_state.
   *
   * @param array $parents
   *   The array of #parents where the widget lives in the form.
   * @param string $field_name
   *   The field name.
   *
   * @return array
   *   The location of processing information within $form_state.
   */
  protected static function getWidgetStateParents(array $parents, $field_name) {
    // Field processing data is placed at
    // $form_state->get(['field_storage', '#parents',
    // ...$parents..., '#fields', $field_name]),
    // to avoid clashes between field names and $parents parts.
    return array_merge(
      ['field_storage', '#parents'], $parents, ['#fields', $field_name]
    );
  }

  /**
   * Ensure that we only inspect the last 7 items of the array.
   *
   * This is done, for example, to deal with differences in the array
   * structure between inline and reusable blocks.
   *
   * @param array $parents
   *   The array of #parents with each resource collection.
   *
   * @return array
   *   The agnostic parents array to use on either inline or reusable blocks.
   */
  protected static function makeParentsAgnostic(array $parents) {
    $reversed_parents = array_reverse($parents);
    if ($reversed_parents[0] === 'links') {
      array_shift($reversed_parents);
    }
    $sliced_parents = array_slice($reversed_parents, 0, 8);
    $reversed_parents = array_reverse($sliced_parents);
    // Retrieve the form element that is using this widget.
    // The structure of $element['#parents'] will be similar to:
    // [0] => field_MACHINE_NAME
    // [1] => 0 (variable counter for field cardinality)
    // [2] => 'resource_items' (static placeholder)
    // [3] => 'items' (static placeholder)
    // [4] => 0 (variable counter for resource item delta)
    // [5] => 'details' (static placeholder)
    // [6] => 'item' (static placeholder)
    // ... where [1] is the field delta and [4] is the resource item delta.
    // Validate that parents array has expected content after massaging it.
    if ($reversed_parents[2] !== 'resource_items' ||
    $reversed_parents[3] !== 'items' ||
    $reversed_parents[5] !== 'details' ||
    $reversed_parents[6] !== 'item') {
      // If the array content has discrepancies, log it into the database.
      \Drupal::logger('utexas_resources')->warning('The resources parents array has changed, this may cause errors when creating or editing content.');
    }
    return [
      'field_machine_name' => $reversed_parents[0],
      'field_delta' => $reversed_parents[1],
      'item_delta' => $reversed_parents[4],
    ];
  }

}
