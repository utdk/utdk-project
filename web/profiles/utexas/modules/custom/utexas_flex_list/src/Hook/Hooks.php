<?php

namespace Drupal\utexas_flex_list\Hook;

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
      'field__utexas_flex_list' => [
        'base hook' => 'field',
      ],
      'field__utexas_flex_list__accordion' => [
        'base hook' => 'field',
      ],
      'field__utexas_flex_list__htabs' => [
        'base hook' => 'field',
      ],
    ];
    return $variables;
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_field_alter')]
  public function themeSuggestionsFieldAlter(&$suggestions, array $variables) {
    $element = $variables['element'];
    if (!isset($element['#field_type']) || $element['#field_type'] !== 'utexas_flex_list') {
      return;
    }
    if (!isset($element['#theme_info']['formatter_name'])) {
      return;
    }
    $count = count($suggestions);
    $key = $count - 1;
    $finished = FALSE;
    while (!$finished) :
      $new_suggestion = $suggestions[$key] . '__' . $element['#theme_info']['formatter_name'];
      array_splice($suggestions, $key + 1, 0, $new_suggestion);
      $key -= 1;
      if ($key === -1) {
        $finished = TRUE;
      }
    endwhile;
  }

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(&$variables) {
    if ($variables['field_type'] === 'utexas_flex_list') {
      $element = $variables['element'];
      $variables['formatter_name'] = $element['#theme_info']['formatter_name'];
      $variables['instance_id'] = $element['#instance_id'] ?? '';
    }
  }

}
