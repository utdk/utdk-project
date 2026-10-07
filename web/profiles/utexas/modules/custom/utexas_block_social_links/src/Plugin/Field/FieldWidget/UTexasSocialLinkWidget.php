<?php

namespace Drupal\utexas_block_social_links\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_block_social_links\Services\UTexasSocialLinkOptions;

/**
 * Plugin implementation of the 'utexas_social_link_widget' widget.
 */
#[FieldWidget(
  id: 'utexas_social_link_widget',
  label: new TranslatableMarkup('UTexas Social Link'),
  field_types: ['utexas_social_link_field']
)]
class UTexasSocialLinkWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $element['headline'] = [
      '#type' => 'textfield',
      '#title' => 'Headline',
      '#description' => $this->t('Provide an optional headline to appear above the icons.'),
      '#default_value' => $items[$delta]->headline ?? '',
    ];
    $element['icon_size'] = [
      '#type' => 'radios',
      '#title' => 'Icon size',
      '#options' => [
        'ut-social-links--small' => $this->t('Small (22px)'),
        'ut-social-links--medium' => $this->t('Medium (40px)'),
        'ut-social-links--large' => $this->t('Large (80px)'),
      ],
      '#default_value' => $items[$delta]->icon_size ?? 'ut-social-links--medium',
    ];

    // Gather the number of links in the form already.
    $stored_links = $items[$delta]->social_account_links ?? '';
    // Bypass requirement to specify allowed classes since they are unknown.
    // phpcs:ignore
    $items = (array) unserialize($stored_links, ['allowed_classes' => TRUE]);
    // Retrieve the form element that is using this widget.
    $parents = [$field_name, 'widget'];
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    // This value is defined/leveraged by ::utexasAddMoreSubmit().
    $item_count = $widget_state[$field_name][$delta]["counter"] ?? NULL;
    // We have to ensure that there is at least one link field.
    if ($item_count === NULL) {
      if (empty($items)) {
        $item_count = 1;
      }
      else {
        $item_count = count($items);
      }
      $widget_state[$field_name][$delta]["counter"] = $item_count;
      static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    }
    // Ensure array keys are consecutive.
    $items = array_values($items);
    $wrapper_id = Html::getUniqueId('ajax-wrapper');
    $element['social_account_links'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];
    for ($i = 0; $i < $item_count; $i++) {
      $element['social_account_links'][$i] = [
        '#type' => 'container',
        '#prefix' => $this->t('Only external URLs allowed.'),
      ];
      $element['social_account_links'][$i]['social_account_name'] = [
        '#type' => 'select',
        '#title' => 'Website',
        '#options' => UTexasSocialLinkOptions::getOptionsArray(),
        '#default_value' => $items[$i]['social_account_name'] ?? NULL,
      ];
      $element['social_account_links'][$i]['social_account_url'] = [
        '#type' => 'url',
        '#title' => 'URL',
        '#default_value' => $items[$i]['social_account_url'] ?? NULL,
        '#description' => $this->t('Example: https://media-site-name.com/our-handle'),
      ];
    }
    $element['social_account_links']['actions']['add'] = [
      '#type' => 'submit',
      '#name' => $field_name . $delta,
      '#value' => $this->t('Add social link item'),
      '#submit' => [[get_class($this), 'utexasAddMoreSubmit']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_class($this), 'utexasAddMoreAjax'],
        'wrapper' => $wrapper_id,
      ],
    ];

    $element['#attached']['library'][] = 'utexas_block_social_links/form';
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
    // This loop is through field instances (not link instances).
    foreach ($values as &$value) {
      $links_to_add = [];
      // Links are stored as a serialized array.
      if (!empty($value['social_account_links'])) {
        foreach ($value['social_account_links'] as $key => $item) {
          if (!empty($item['social_account_url'])) {
            // Only save links that have data.
            $links_to_add[] = $value['social_account_links'][$key];
          }
        }
        if (!empty($links_to_add)) {
          $value['social_account_links'] = serialize($links_to_add);
        }
      }
    }

    return $values;
  }

}
