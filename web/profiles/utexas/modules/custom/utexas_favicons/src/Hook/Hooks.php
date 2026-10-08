<?php

namespace Drupal\utexas_favicons\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_preprocess_html().
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(&$variables) {
    // Add meta tags for the different favicons and touch icons.
    $manifest = [
      '#tag' => 'link',
      '#attributes' => [
        'rel' => 'manifest',
        'href' => '/site.webmanifest',
      ],
    ];
    $variables['page']['#attached']['html_head'][] = [$manifest, 'manifest'];

    $icons = [
      [
        'rel' => 'apple-touch-icon',
        'sizes' => '180x180',
        'filename' => 'apple-touch-icon.png',
      ],
      [
        'sizes' => '16x16',
        'filename' => 'favicon.ico',
        'rel' => 'icon',
      ],
      [
        'sizes' => '32x32',
        'filename' => 'favicon-32x32.png',
      ],
      [
        'sizes' => '48x48',
        'filename' => 'favicon-48x48.png',
      ],
      [
        'sizes' => '150x150',
        'filename' => 'mstile-150x150.png',
      ],
      [
        'sizes' => '192x192',
        'filename' => 'android-chrome-192x192.png',
        'rel' => 'icon',
      ],
      [
        'sizes' => '512x512',
        'filename' => 'android-chrome-512x512.png',
        'rel' => 'icon',
      ],
      [
        'sizes' => '400x400',
        'filename' => 'safari-pinned-tab.svg',
        'color' => '#bf5700',
        'rel' => 'mask-icon',
      ],
    ];

    foreach ($icons as $value) {
      $sized_icons = [
        '#tag' => 'link',
        '#attributes' => [
          'rel' => $value['rel'] ?? 'icon',
          'sizes' => $value['sizes'],
          'href' => '/' . $value['filename'],
        ],
      ];
      if (isset($value['color'])) {
        $sized_icons['#attributes']['color'] = $value['color'];
      }
      $variables['page']['#attached']['html_head'][] = [$sized_icons, $value['sizes']];
    }
    $favicon = [
      '#tag' => 'link',
      '#attributes' => [
        'rel' => 'icon',
        'href' => '/favicon.ico',
        'type' => 'image/vnd.microsoft.icon',
      ],
    ];
    $variables['page']['#attached']['html_head'][] = [$favicon, 'favicon'];
  }

}
