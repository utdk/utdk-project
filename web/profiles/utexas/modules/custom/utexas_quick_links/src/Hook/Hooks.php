<?php

namespace Drupal\utexas_quick_links\Hook;

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
      'utexas_quick_links' => [
        'variables' => [
          'headline' => NULL,
          'copy' => NULL,
          'links' => [],
        ],
        'template' => 'utexas-quick-links',
      ],
    ];
    return $variables;
  }

}
