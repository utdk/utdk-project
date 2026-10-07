<?php

namespace Drupal\utexas_flex_content_area\Hook;

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
      'utexas_flex_content_area' => [
        'variables' => [
          'media' => NULL,
          'media_ratio' => NULL,
          'headline' => NULL,
          'copy' => NULL,
          'links' => [],
          'cta' => NULL,
        ],
        'template' => 'utexas-flex-content-area',
      ],
    ];
    return $variables;
  }

}
