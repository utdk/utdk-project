<?php

namespace Drupal\utexas_qualtrics_filter\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for utexas_qualtrics_filter.
 */
class Hooks {
  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Main module help for the utexas_qualtrics_filter module.
      case 'help.page.utexas_qualtrics_filter':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';
        $output .= '<p>' . $this->t('Custom text filter to allow Qualtrics forms into WYSIWYG fields') . '</p>';
        return $output;

      case 'help.page.ckeditor_qualtrics_button':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';
        $output .= '<p>' . $this->t('Allows to insert a Qualtrics button in CKEditor.') . '</p>';
        return $output;

      default:
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public static function theme() {
    return [
      'utexas_qualtrics_filter' => [
        'render element' => 'children',
      ],
    ];
  }

}
