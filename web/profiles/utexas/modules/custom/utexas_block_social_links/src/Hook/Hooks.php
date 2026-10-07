<?php

namespace Drupal\utexas_block_social_links\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field__field_utexas_sl_social_links')]
  public function preprocessFieldFieldUtexasSlSocialLinks(&$variables) {
    $variables['icon_size'] = $variables['element']['#icon_size'];
  }

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(&$variables) {
    $current_path = \Drupal::service('path.current')->getPath();
    if ($current_path === '/admin/structure/social-links') {
      $variables['#attached']['library'][] = 'utexas_block_social_links/form';
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    return [
      'field__field_utexas_sl_social_links' => [
        'base hook' => 'field',
        'template' => 'field--utexas-social-link-field',
      ],
    ];
  }

}
