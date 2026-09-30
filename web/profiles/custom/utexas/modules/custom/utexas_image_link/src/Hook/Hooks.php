<?php

namespace Drupal\utexas_image_link\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    $variables = [
      'utexas_image_link' => [
        'variables' => [
          'image' => NULL,
          'link' => NULL,
        ],
        'template' => 'utexas-image-link',
      ],
    ];
    return $variables;
  }

}
