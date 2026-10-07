<?php

namespace Drupal\utexas_form_elements\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Drupal\Core\Url;
use Drupal\utexas_form_elements\UtexasLinkOptionsElementHelper;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;

/**
 * Defines an element for a single link + title field, including options.
 */
#[FormElement('utexas_link_options_element')]
class UtexasLinkOptionsElement extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#theme' => 'utexas_link_options_element',
      '#tree' => TRUE,
      '#process' => [
        [$class, 'processLinkOptionsElement'],
      ],
    ];
  }

  /**
   * Process handler for the link options form element.
   */
  public static function processLinkOptionsElement(&$element, FormStateInterface $form_state, &$form) {
    // Add validation for the title element.
    $element['#element_validate'][] = ['Drupal\link\Plugin\Field\FieldWidget\LinkWidget', 'validateTitleNoLink'];

    // Link URL form element.
    $linkit_profile_id = 'flex_html';
    $standard_description = t('<div class="description">Start typing the title of a piece of content to select it. You can also enter an internal path such as %internal or an external URL such as %external. Enter %front to link to the front page.</div>', [
      '%internal' => '/node/add',
      '%external' => 'https://example.com',
      '%front' => '<front>',
    ]);
    $element['uri'] = [
      '#type' => 'linkit',
      '#title' => t('URL'),
      '#default_value' => isset($element['#default_value']['uri']) ? static::getUriAsDisplayableString($element['#default_value']['uri']) : '',
      '#maxlength' => 2048,
      '#required' => $element['#required'],
      '#description' => $element['#description'] ?? $standard_description,
      '#description_display' => 'before',
      '#autocomplete_route_name' => 'linkit.autocomplete',
      '#autocomplete_route_parameters' => [
        'linkit_profile_id' => $linkit_profile_id,
      ],
      '#element_validate' => [[get_called_class(), 'validateUriElement']],
    ];

    // Link title form element.
    $element['title'] = [
      '#type' => 'textfield',
      '#title' => t('Link text'),
      '#description_display' => 'before',
      '#default_value' => $element['#default_value']['title'] ?? $element['#default_value']['title'],
      '#access' => isset($element['#suppress_title_display']) ? FALSE : TRUE,
      '#description' => $element['#title_description'] ?? '',
    ];

    if (!empty($element['title']['#default_value'])) {
      $element['plaintitle'] = [
        '#markup' => Unicode::Truncate($element['title']['#default_value'], 40, FALSE, TRUE),
      ];
    }
    if (!empty($element['uri']['#default_value'])) {
      $element['plainuri'] = [
        '#markup' => Unicode::Truncate($element['uri']['#default_value'], 40, FALSE, TRUE),
      ];
    }

    // Add link options form element.
    $link_options_helper = new UtexasLinkOptionsHelper();
    $element = $link_options_helper->addLinkOptions($element);

    // Attach library to help with element css styles.
    $element['#attached']['library'][] = 'utexas_form_elements/link-element';

    return $element;
  }

  /**
   * Gets the URI without the 'internal:' or 'entity:' scheme.
   *
   * @param string $uri
   *   The URI to get the displayable string for.
   *
   * @return string
   *   The displayble string to be shown as the link value.
   *
   * @see Drupal\linkit\Plugin\Field\FieldWidget\Linkitwidget\formElement()
   */
  protected static function getUriAsDisplayableString($uri) {
    $uri = UtexasLinkOptionsHelper::handlePhoneNumbers($uri);
    /* Start borrowed section. */
    // Borrowed (as of 4/12/20) from future Linkit widget patch in the Linkit
    // module. (https://www.drupal.org/project/linkit/issues/2712951)
    $uri_scheme = parse_url($uri, PHP_URL_SCHEME);
    if (!empty($uri) && empty($uri_scheme)) {
      $uri = UtexasLinkOptionsElementHelper::uriFromUserInput($uri);
      $uri_scheme = parse_url($uri, PHP_URL_SCHEME);
    }
    $uri_as_url = !empty($uri) ? Url::fromUri(rawurldecode($uri))->toString() : '';
    /* End borrowed section. */

    // @todo '<front>' is valid input for BC reasons, may be removed by
    //   https://www.drupal.org/node/2421941
    // Display 'internal:/' as '<front>' in form element.
    if (!empty($uri) && $uri_scheme === 'internal') {
      $uri_reference = explode(':', $uri, 2)[1];
      $path = parse_url($uri, PHP_URL_PATH);
      if ($path === '/') {
        $uri_as_url = '<front>' . substr($uri_reference, 1);
      }
    }

    return $uri_as_url;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // If input is FALSE or there is no value for the uri, return default
    // value(s) of the element. In this case, we don't allow for user default
    // settings so we just set the whole element to NULL.
    if ($input === FALSE || !isset($input['uri'])) {
      // Do not store anything in the field for this element value.
      return NULL;
    }

    // Start with unaltered input.
    $processed_input = $input;

    // Convert user input to a uri string with schema if needed, for storage.
    // $input['uri'] must not be null, but that is checked above and is also
    // explicitly validated.
    $processed_input['uri'] = UtexasLinkOptionsElementHelper::uriFromUserInput($input['uri']);

    // Setting these values in the $processed_input should be enough. But
    // for reasons not entirely understood, if this element is nested within
    // another element, the altered input is unused by the parent element.
    // We set the values directly in the $form_state here as a workaround.
    $uri_parents = $element['#parents'];
    array_push($uri_parents, 'uri');
    $uri_value = UtexasLinkOptionsElementHelper::uriFromUserInput($input['uri']);
    NestedArray::setValue($form_state->getValues(), $uri_parents, $uri_value);

    // Check target values for _blank. If _blank is found, add helpful rel
    // values related to preventing "reverse tabnabbing". These values are not
    // stored because there is not a corresponding form element declared.
    if (isset($input['options']['attributes']['target'])) {
      if ($input['options']['attributes']['target']['_blank']) {
        $processed_input['options']['attributes']['rel'] = [
          'noopener',
          'noreferrer',
        ];

        // Setting these values in the $processed_input should be enough. But
        // for reasons not entirely understood, if this element is nested within
        // another element, the altered input is unused by the parent element.
        // We set the values directly in the $form_state here as a workaround.
        $rel_parents = $element['#parents'];
        array_push($rel_parents, 'options');
        array_push($rel_parents, 'attributes');
        array_push($rel_parents, 'rel');
        $rel_value = [
          'noopener',
          'noreferrer',
        ];
        NestedArray::setValue($form_state->getValues(), $rel_parents, $rel_value);
      }
    }

    // Do not store class key if the input is "none" (has a key of "0").
    if (isset($input['options']['attributes']['class'])) {
      if ($input['options']['attributes']['class'] != '0') {
        $processed_input['options']['attributes']['class'] = $input['options']['attributes']['class'];

        // Setting these values in the $processed_input should be enough. But
        // for reasons not entirely understood, if this element is nested within
        // another element, the altered input is unused by the parent element.
        // We set the values directly in the $form_state here as a workaround.
        $class_parents = $element['#parents'];
        array_push($class_parents, 'options');
        array_push($class_parents, 'attributes');
        array_push($class_parents, 'class');
        $class_value = $input['options']['attributes']['class'];
        NestedArray::setValue($form_state->getValues(), $class_parents, $class_value);
      }
      else {
        unset($processed_input['options']['attributes']['class']);
      }
    }

    return $processed_input;
  }

  /**
   * Form element validation handler for the 'uri' element.
   *
   * Disallows saving inaccessible or untrusted URLs.
   *
   * @see Drupal\link\Plugin\Field\FieldWidget\LinkWidget
   */
  public static function validateUriElement($element, FormStateInterface $form_state, $form) {
    $uri = UtexasLinkOptionsElementHelper::uriFromUserInput($element['#value']) ?? '';
    $form_state->setValueForElement($element, $uri);

    // If getUserEnteredStringAsUri() mapped the entered value to a 'internal:'
    // URI , ensure the raw value begins with '/', '?' or '#'.
    // @todo '<front>' is valid input for BC reasons, may be removed by
    //   https://www.drupal.org/node/2421941
    $input_host = parse_url($element['#value'], PHP_URL_HOST);
    $input_path = parse_url($element['#value'], PHP_URL_PATH);
    $request_host = \Drupal::request()->getHost();
    if ($input_host === $request_host) {
      $form_state->setError($element, t('Links to the current site should be entered as a relative path (e.g., enter <strong>%relative</strong> instead of %absolute)', [
        '%absolute' => \Drupal::request()->getSchemeAndHttpHost() . $input_path,
        '%relative' => $input_path,
      ]));
    }
    elseif (
      parse_url($uri, PHP_URL_SCHEME) === 'internal' &&
      !in_array($element['#value'][0], ['/', '?', '#'], TRUE) &&
      substr($element['#value'], 0, 7) !== '<front>'
    ) {
      $form_state->setError($element, t('Manually entered paths should start with one of the following characters: / ? #'));
      return;
    }
  }

}
