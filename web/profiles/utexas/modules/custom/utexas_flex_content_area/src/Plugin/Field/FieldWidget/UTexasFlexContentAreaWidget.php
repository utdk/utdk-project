<?php

namespace Drupal\utexas_flex_content_area\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\UtexasWidgetBase;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Plugin implementation of the 'utexas_flex_content_area' widget.
 */
#[FieldWidget(
  id: 'utexas_flex_content_area',
  label: new TranslatableMarkup('Flex Content Area'),
  field_types: ['utexas_flex_content_area']
)]
class UTexasFlexContentAreaWidget extends UtexasWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Get the form item that this widget is being applied to.
    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];

    $allowed_bundles = MediaEntityImageHelper::getAllowedBundles();
    array_push($allowed_bundles, 'utexas_video_external');

    $field_name = $this->fieldDefinition->getName();
    $headline = $item->headline ? 'Flex Content Area ' . ($delta + 1) . ' (' . $item->headline . ')' : 'New item';
    $element['flex_content_area'] = [
      '#type' => 'details',
      '#title' => $this->t('%headline', ['%headline' => $headline]),
    ];
    $element['flex_content_area']['image'] = [
      '#type' => 'media_library',
      '#description' => $this->t('If using an image, note that it will be scaled and cropped to 3:2 ratio. Ideally, upload an image of 1000x666 pixels to maintain resolution & avoid cropping.'),
      '#allowed_bundles' => $allowed_bundles,
      '#delta' => $delta,
      '#cardinality' => 1,
      '#title' => $this->t('Media'),
      '#default_value' => MediaEntityImageHelper::checkMediaExists($item->image),
    ];
    $element['flex_content_area']['headline'] = [
      '#title' => 'Headline',
      '#type' => 'textfield',
      '#description' => $this->t('To make this headline into a hyperlink, enter a URL in the Call to Action URL field below.'),
      '#default_value' => $item->headline ?? NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element['flex_content_area']['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => $item->copy_value ?? NULL,
      '#format' => $item->copy_format ?? 'restricted_html',
    ];
    // Retrieve the form element that is using this widget.
    $parents = [$field_name, 'widget'];
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    // This value is defined/leveraged by ::utexasAddMoreSubmit().
    $link_count = $widget_state[$field_name][$delta]["counter"] ?? NULL;
    // We have to ensure that there is at least one link field.
    // phpcs:ignore
    $links = !empty($item->links) ? unserialize($item->links) : [];
    if ($link_count === NULL) {
      if (empty($links)) {
        $link_count = 1;
      }
      else {
        $link_count = count($links);
      }
      $widget_state[$field_name][$delta]["counter"] = $link_count;
      static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    }
    $wrapper_id = Html::getUniqueId('ajax-wrapper');
    $element['flex_content_area']['links'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('List of links'),
    ];
    $element['flex_content_area']['links']['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['flex_content_area']['links']['#suffix'] = '</div>';
    // Ensure array keys are consecutive.
    $links = array_values($links);
    for ($i = 0; $i < $link_count; $i++) {
      $element['flex_content_area']['links'][$i] = [
        '#type' => 'utexas_link_options_element_multiple',
        '#default_value' => [
          'uri' => $links[$i]['uri'] ?? '',
          'title' => $links[$i]['title'] ?? '',
          'options' => $links[$i]['options'] ?? [],
        ],
      ];
    }
    // We limit form validation so that other elements are not validated
    // during this submit button's refresh action. See
    // See https://www.drupal.org/project/drupal/issues/2476569
    $element['flex_content_area']['links']['actions']['add_link'] = [
      '#type' => 'submit',
      '#name' => $field_name . $delta,
      '#value' => $this->t('Add link'),
      '#submit' => [[get_class($this), 'utexasAddMoreSubmit']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_class($this), 'utexasAddMoreAjax'],
        'wrapper' => $wrapper_id,
      ],
    ];
    $element['flex_content_area']['cta_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Call to Action'),
    ];
    $element['flex_content_area']['cta_wrapper']['link'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $item->link_uri ?? NULL,
        'title' => $item->link_text ?? NULL,
        'options' => $item->link_options ?? [],
      ],
      '#title_description' => $this->t('Optional. Leave blank to link only the item headline. Add text to print a second link at the bottom of the item.'),
    ];
    $element['#attached']['library'][] = 'utexas_flex_content_area/widget';
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
    array_pop($element['#parents']);
    // The field_delta will be the last (nearest) element in the #parents array.
    $field_delta = array_pop($element['#parents']);
    // The field_name will be the penultimate element in the #parents array.
    $field_name = array_pop($element['#parents']);
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
   * Selects and returns the fieldset with the items in it.
   */
  public static function utexasAddMoreAjax(array &$form, FormStateInterface $form_state) {
    return self::retrieveAddMoreElement($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through (potential) field instances.
    $return = [];
    foreach ($values as $delta => $wrapper) {
      $value = $wrapper['flex_content_area'];
      // A null media value should be saved as 0.
      if (empty($value['image'])) {
        $value['image'] = 0;
      }
      // Links are stored as a serialized array.
      $links_to_add = [];
      if (!empty($value['links'])) {
        foreach ($value['links'] as $key => $link) {
          if (!empty($link['uri'])) {
            // Only add links with actual content.
            $links_to_add[] = $value['links'][$key];
          }
        }
        // Don't serialize an empty array.
        if (!empty($links_to_add)) {
          $value['links'] = serialize($links_to_add);
        }
        else {
          unset($value['links']);
        }
      }
      // A null headline value should be removed so that the twig template
      // can easily check for an empty value.
      if (empty($value['headline'])) {
        unset($value['headline']);
      }
      if (isset($value['cta_wrapper']['link']['uri'])) {
        $value['link_uri'] = $value['cta_wrapper']['link']['uri'];
        $value['link_text'] = $value['cta_wrapper']['link']['title'] ?? '';
        $value['link_options'] = $value['cta_wrapper']['link']['options'] ?? [];
      }
      $value['copy_value'] = $value['copy']['value'];
      $value['copy_format'] = $value['copy']['format'];
      $return[$delta] = $value;
    }

    return $return;
  }

}
