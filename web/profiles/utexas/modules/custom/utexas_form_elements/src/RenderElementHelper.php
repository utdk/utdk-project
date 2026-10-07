<?php

namespace Drupal\utexas_form_elements;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides functionality to process render elements.
 */
class RenderElementHelper {

  /**
   * Alters the element type info.
   *
   * @param array $info
   *   An associative array with structure identical to that of the return value
   *   of \Drupal\Core\Render\ElementInfoManagerInterface::getInfo().
   */
  public function alterElementInfo(array &$info) {
    foreach ($info as $element_type => $element_info) {
      $info[$element_type]['#process'][] = [static::class, 'processElement'];
    }
  }

  /**
   * Process all render elements.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element. Note that $element must be taken by reference here, so processed
   *   child elements are taken over into $form_state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processElement(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $field_types_to_affect = [
      'checkboxes',
      'email',
      'entity_autocomplete',
      'link',
      'managed_file',
      'number',
      'password',
      'radio',
      'radios',
      'select',
      'textarea',
      'textfield',
    ];

    // Known fields that are not compatible with the `description_display`
    // setting: date, link, item, Media Library, textarea with text format.
    if (in_array($element['#type'], $field_types_to_affect)) {
      // Position Form API field descriptions directly below their field labels.
      // Note: we should consider removing & replacing this if and when
      // https://www.drupal.org/node/2318757 becomes available.
      $element['#description_display'] = 'before';
    }

    return $element;
  }

  /**
   * Allow a subset of markup in single-line text fields.
   *
   * @param string $value
   *   The single-line text field value to process.
   * @param bool $renderable
   *   Whether or not a render object should be returned.
   *
   * @return array
   *   The processed element.
   */
  public static function filterSingleLineText($value, $renderable = FALSE) {
    // Skip processing null/empty values.
    if (!$value) {
      return $value;
    }
    $prepared = strip_tags($value, '<em><i><strong><b><sub><sup><cite><wbr><span><abbr>');
    if ($renderable) {
      $prepared = ['#markup' => $prepared];
    }
    return $prepared;
  }

}
