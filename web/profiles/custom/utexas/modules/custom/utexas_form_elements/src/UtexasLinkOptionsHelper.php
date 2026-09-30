<?php

namespace Drupal\utexas_form_elements;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;

/**
 * Business logic for rendering the listing view.
 */
class UtexasLinkOptionsHelper {
  use StringTranslationTrait;

  /**
   * Helper function to add link options form elements.
   *
   * @param array $element
   *   The element array.
   * @param Drupal\link\LinkItemInterface|null $item
   *   The link item.
   *
   * @return array
   *   The link element with options added.
   */
  public function addLinkOptions(array $element, ?LinkItemInterface $item = NULL) {
    // Add validation for the two listed elements to the parent element.
    $element['#element_validate'][] = [get_called_class(), 'validateLinkUri'];
    $element['#element_validate'][] = [get_called_class(), 'validateLinkOptionsTarget'];
    $element['#element_validate'][] = [get_called_class(), 'validateLinkOptionsClass'];

    // Handle target attribute form element.
    $target_options = [
      '_blank' => $this->t('Open in new window/tab'),
    ];
    $element['options']['attributes']['target'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Link Behavior'),
      '#description_display' => 'before',
      '#description' => $this->t('Recommendation: append an external link icon, below, when using this setting. See <a href="https://www.w3.org/TR/WCAG-TECHS/G201.html">WCAG G201</a>.'),
      '#options' => $target_options,
      '#attributes' => [
        'class' => [
          'utexas-link-options-attributes-target',
        ],
      ],
      '#access' => isset($element['#suppress_options_target_display']) ? FALSE : TRUE,
    ];

    // Handle lock icons using class attribute form element.
    $class_options = [
      '0' => $this->t('No icon'),
      'ut-cta-link--lock' => $this->t('Authentication required icon <span class="ut-cta-link--lock"></span>'),
      'ut-cta-link--external' => $this->t('External link icon <span class="ut-cta-link--external"></span>'),
      'ut-cta-link--angle-right' => $this->t('Right-facing caret <span class="ut-cta-link--angle-right"></span>'),
    ];
    $element['options']['attributes']['class'] = [
      '#type' => 'radios',
      '#title' => $this->t('Link Appearance'),
      '#options' => $class_options,
      '#access' => isset($element['#suppress_options_class_display']) ? FALSE : TRUE,
    ];

    $element['#attached']['library'][] = 'utexas_form_elements/link-options';

    // Add defaults. Different methods of default assignment are needed for
    // widgets vs. form elements.
    $element = $this->addLinkOptionsDefaults($element, $item);

    return $element;
  }

  /**
   * Helper function to add defaults to link options.
   *
   * @param array $element
   *   The element array.
   * @param Drupal\link\LinkItemInterface|null $item
   *   The link item.
   *
   * @return array
   *   The link element with link option defaults added.
   */
  private function addLinkOptionsDefaults(array $element, ?LinkItemInterface $item = NULL) {
    // When a widget calls this method, we use the $item to access an 'options'
    // object. When another form element calls this method, we can access the
    // 'options' array directly.
    $options = isset($item) ? $item->get('options')->getValue() : $element['#default_value']['options'];

    $default_target_value = !empty($options['attributes']['target']) ? $options['attributes']['target'] : [];
    $default_class_value = !empty($options['attributes']['class']) ? $options['attributes']['class'] : '0';

    $element['options']['attributes']['target']['#default_value'] = $default_target_value;
    $element['options']['attributes']['class']['#default_value'] = $default_class_value;

    return $element;
  }

  /**
   * Helper function to construct a link with options.
   *
   * @param array $item
   *   The field item link array.
   * @param array $link_add_classes
   *   Classes to add to the link (e.g. 'ut-btn', 'ut-link--darker').
   * @param string $link_title_override
   *   Override the link title using a value not in the $item object.
   *
   * @return object
   *   The prepared link object.
   */
  public static function buildLink(array $item, array $link_add_classes = [], ?string $link_title_override = NULL) {
    // If no uri, return null.
    if (empty($item['link']['uri'])) {
      return NULL;
    }

    $item['link']['uri'] = self::handlePhoneNumbers($item['link']['uri']);

    // @todo fix up data at the source (UtexasLinkOptionsElement).
    // Clean up some less than ideal data.
    if (isset($item['link']['options']['attributes']['class']) && $item['link']['options']['attributes']['class'] === "0") {
      unset($item['link']['options']['attributes']['class']);
    }
    if (isset($item['link']['options']['attributes']['target']['_blank']) && $item['link']['options']['attributes']['target']['_blank'] === 0) {
      unset($item['link']['options']['attributes']['target']);
    }
    // The class is being passed in as a string. It should be an array.
    if (isset($item['link']['options']['attributes']['class'])) {
      $class_array = (array) $item['link']['options']['attributes']['class'];
      $item['link']['options']['attributes']['class'] = $class_array;
    }

    $item_link_option_classes = $item['link']['options']['attributes']['class'] ?? [];
    $item['link']['options']['attributes']['class'] = array_merge($item_link_option_classes, $link_add_classes);

    // Get link title.
    $item_link_title = $item['link']['title'] ?? "";

    // Override the link title text if need be.
    $link_title = $link_title_override ?? $item_link_title;
    // Ensure that links without title text print the URL as title text.
    if ($link_title == "") {
      // Create a dummy URL object because we want to set it to absolute in
      // order to retrieve a "pretty" string representation.
      $temp_url = Url::fromUri(rawurldecode($item['link']['uri']));
      $temp_url->setAbsolute();
      $link_title = rawurldecode($temp_url->toString());
    }

    $link_url = Url::fromUri(rawurldecode($item['link']['uri']), $item['link']['options']);
    $link = Link::fromTextAndUrl(RenderElementHelper::filterSingleLineText($link_title, TRUE), $link_url);

    return $link;
  }

  /**
   * Form element validation handler for the 'options' => 'target' element.
   *
   * Requires the URL value if an options target was filled in.
   */
  public static function validateLinkOptionsTarget(&$element, FormStateInterface $form_state, $form) {
    if ($element['uri']['#value'] === '' && $element['options']['attributes']['target']['#value'] !== []) {
      $form_state->setError($element['options']['attributes']['target'],
      t('The @uri field is required when the @target field is specified.',
        [
          '@target' => $element['options']['attributes']['target']['#title'],
          '@uri' => $element['uri']['#title'],
        ]
        )
      );
    }
  }

  /**
   * Form element validation handler for valid link URIs.
   *
   * Requires the URL value if an options class was filled in.
   */
  public static function validateLinkUri(&$element, FormStateInterface $form_state, $form) {

    if (!empty($element['uri']['#value'])) {
      $test_uri = UtexasLinkOptionsElementHelper::uriFromUserInput($element['uri']['#value']);
      try {
        Url::fromUri(rawurldecode($test_uri));
      }
      catch (\Exception $e) {
        $form_state->setError(
          $element['uri'],
          t(
            'The URL @uri does not appear to be valid.',
            ['@uri' => $element['uri']['#value']]
          )
        );
      }
    }
  }

  /**
   * Form element validation handler for the 'options' => 'class' element.
   *
   * Requires the URL value if an options class was filled in.
   */
  public static function validateLinkOptionsClass(&$element, FormStateInterface $form_state, $form) {
    if ($element['uri']['#value'] === '' && $element['options']['attributes']['class']['#value'] !== '0') {
      $form_state->setError($element['options']['attributes']['class'],
        t('The @uri field is required when the @class field is specified.',
        ['@class' => $element['options']['attributes']['class']['#title'], '@uri' => $element['uri']['#title']]));
    }
  }

  /**
   * Handle edge cases with phone number formatting.
   *
   * @param string $uri
   *   A string like "tel:123".
   *
   * @return string
   *   The prepared string, such as "tel:+1-123".
   */
  public static function handlePhoneNumbers($uri) {
    if (str_starts_with($uri, 'tel:')) {
      // See https://www.drupal.org/project/drupal/issues/2484693.
      // If the telephone number is 5 or less digits, parse_url() will think
      // it's a port number rather than a phone number which causes the link
      // formatter to throw an InvalidArgumentException. Avoid this by inserting
      // a dash (-) after the first digit - RFC 3966 defines the dash as a
      // visual separator character and so will be removed before the phone
      // number is used. See https://bugs.php.net/bug.php?id=70588 for more.
      // While the bug states this only applies to numbers <= 65535, a 5 digit
      // number greater than 65535 will cause parse_url() to return FALSE so
      // we need the work around on any 5 digit (or less) number.
      // First we strip whitespace so we're counting actual digits.
      $phone_number = preg_replace('/\s+/', '', $uri);
      $phone_number = preg_replace('/tel:/', '', $phone_number);
      if (strlen($phone_number) <= 5) {
        $uri = 'tel:+1-' . $phone_number;
      }
    }
    return $uri;
  }

}
