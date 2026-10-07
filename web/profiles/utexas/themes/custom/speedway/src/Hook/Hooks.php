<?php

namespace Drupal\speedway\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Link;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    $search_forms = [
      'utexas_search_form',
      'search_block_form',
    ];
    if (!in_array($form_id, $search_forms)) {
      $form['actions']['submit']['#attributes']['class'][] = 'ut-btn';
      $form['actions']['reset']['#attributes']['class'][] = 'ut-btn--secondary';
    }
    if (in_array($form_id, $search_forms)) {
      $form['#attributes']['class'][] = 'ut-search-form';
    }
  }

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables) {
    $current_route = \Drupal::routeMatch();
    $route_name = $current_route->getRouteName();
    $theme_settings = \Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider');

    // Provide a {{ main_content_attributes }} object to all pages.
    $variables['main_content_attributes'] = new Attribute();
    $variables['main_content_attributes']->setAttribute('role', 'main');
    // Default classes for page.html.twig.
    $variables['main_content_attributes']->addClass(['layout-content', 'col']);

    // Logo height defaults to short unless otherwise specified.
    $logo_height = $theme_settings->getSetting('logo_height') ?? 'short_logo';
    $variables['logo_height'] = str_replace('_', '-', $logo_height);
    // Parent entity.
    $variables['parent_entity_title'] = $theme_settings->getSetting('parent_link_title');
    $variables['parent_entity_link'] = $theme_settings->getSetting('parent_link');
    if (!empty($variables['parent_entity_title']) && !empty($variables['parent_entity_link'])) {
      if ($url = Url::fromUri($theme_settings->getSetting('parent_link'))) {
        $variables['parent_entity'] = [
          'prefix' => ['#markup' => '<span class="ut-parent-entity">'],
          'link' => Link::fromTextAndUrl($variables['parent_entity_title'], $url)->toRenderable(),
          'suffix' => ['#markup' => '</span>'],
        ];
      }
    }
    // Modify classes for certain page routes.
    if ($route_name === 'utexas_google_search.search') {
      $variables['main_content_attributes']->removeClass(['col']);
      $variables['main_content_attributes']->addClass([
        'search-results-page',
      ]);
    }
  }

  /**
   * Implements hook_preprocess_region().
   */
  #[Hook('preprocess_region')]
  public function preprocessRegion(&$variables) {
    $theme_settings = \Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider');
    // Add Bootstrap wrapping divs to regions that should be containerized.
    if (in_array($variables['region'], [
      'breadcrumb',
      'help',
      'highlighted',
    ])) {
      $variables['add_bootstrap_container'] = TRUE;
    }

    if ($variables['region'] === 'header_secondary') {
      // Add class when theme setting has been set to "side-by-side".
      if ($theme_settings->getSetting('header_secondary_display') !== 'side_by_side') {
        $variables['attributes']['class'][] = 'flex-column row-gap-2 align-items-end';
      }
      else {
        $variables['attributes']['class'][] = 'align-items-center';
      }
    }
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    $base_plugin_id = $variables['base_plugin_id'];
    // Add logo alt text.
    if ($base_plugin_id === 'system_branding_block') {
      $config = \Drupal::config('system.site');
      $variables['logo_alt_text'] = $config->get('name');
    }
    if (in_array($base_plugin_id, ['menu_block', 'system_menu_block'])) {
      $theme_settings = \Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider');
      $region = $variables['elements']['#utexas_layouts_region'] ?? '';
      if ($region === 'primary_menu') {
        // Add class when main menu alignment setting is checked.
        $alignment = $theme_settings->getSetting('main_menu_alignment');
        if ($alignment === 'left_alignment') {
          $variables['content']['#attributes']['class'][] = 'justify-content-start';
        }
        elseif ($alignment === 'right_alignment') {
          $variables['content']['#attributes']['class'][] = 'justify-content-end';
        }
        else {
          $variables['content']['#attributes']['class'][] = 'justify-content-between';
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_views_view().
   *
   * Adds BC classes that were previously added by the Views module.
   */
  #[Hook('preprocess_views_view')]
  public function preprocessViewsView(&$variables): void {
    if (!empty($variables['attributes']['class'])) {
      $bc_classes = preg_replace('/[^a-zA-Z0-9- ]/', '-', $variables['attributes']['class']);
      $variables['attributes']['class'] = array_merge($variables['attributes']['class'], $bc_classes);
    }
    if (!empty($variables['css_class'])) {
      $existing_classes = explode(' ', $variables['css_class']);
      $bc_classes = preg_replace('/[^a-zA-Z0-9- ]/', '-', $existing_classes);
      $variables['css_class'] = implode(' ', array_merge($existing_classes, $bc_classes));
    }
  }

}
