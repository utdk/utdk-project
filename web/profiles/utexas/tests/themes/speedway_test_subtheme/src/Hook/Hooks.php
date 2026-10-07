<?php

namespace Drupal\speedway_test_subtheme\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(&$variables) {
    $theme_settings = \Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider');
    // This regular expression looks for link title text like the following:
    // [First Parent](https://utexas.edu) | [Second Parent](https://utexas.edu).
    $parent_entity_text = $theme_settings->getSetting('parent_link_title');
    if (!empty($parent_entity_text)) {
      preg_match_all('/\[(.*)\]\((.*)\) | \[(.*)\]\((.*)\)/', $parent_entity_text, $matches);
      if (isset($matches[4])) {
        $first_parent_url = Url::fromUri($matches[2][0]);
        $first_parent_text = $matches[1][0];
        $first_parent = Link::fromTextAndUrl($first_parent_text, $first_parent_url)->toRenderable();
        $second_parent_url = Url::fromUri($matches[4][1]);
        $second_parent_text = $matches[3][1];
        $second_parent = Link::fromTextAndUrl($second_parent_text, $second_parent_url)->toRenderable();
        $variables['parent_entity'] = 'foo';
        $variables['parent_entity'] = [
          'pre' => ['#markup' => '<span class="ut-double-parent">'],
          'first' => $first_parent,
          'second' => $second_parent,
          'post' => ['#markup' => '</span>'],
        ];
      }
    }
  }

}
