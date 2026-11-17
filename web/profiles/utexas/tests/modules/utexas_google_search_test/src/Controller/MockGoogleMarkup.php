<?php

namespace Drupal\utexas_google_search_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mock data to test Google PSE accessibility tweaks.
 */
class MockGoogleMarkup extends ControllerBase {

  /**
   * Creates a render array for the search page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   The search form and search results build array.
   */
  public function view(Request $request) {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('utexas_google_search_test')->getPath();
    $build['#markup'] = file_get_contents($module_path . '/assets/test-content.html');
    $build['#attached']['library'][] = 'utexas_google_search/accessibilityTweaks';
    return $build;
  }

}
