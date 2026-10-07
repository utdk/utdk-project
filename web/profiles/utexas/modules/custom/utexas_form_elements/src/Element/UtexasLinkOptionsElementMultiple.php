<?php

namespace Drupal\utexas_form_elements\Element;

use Drupal\Core\Render\Attribute\FormElement;

/**
 * Defines an element for a multiple link + title fields, including options.
 */
#[FormElement('utexas_link_options_element_multiple')]
class UtexasLinkOptionsElementMultiple extends UtexasLinkOptionsElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#theme' => 'utexas_link_options_element_multiple',
      '#tree' => TRUE,
      '#process' => [
        [$class, 'processLinkOptionsElement'],
      ],
    ];
  }

}
