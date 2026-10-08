<?php

declare(strict_types=1);

namespace Drupal\utexas_form_elements\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Link Styles plugin.
 */
class LinkOptions extends CKEditor5PluginDefault {

  /**
   * {@inheritDoc}
   *
   * Adds options to the link ckeditor plugin.
   *
   * See https://ckeditor.com/docs/ckeditor5/latest/features/link.html#custom-link-attributes-decorators
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {

    $theme = \Drupal::configFactory()->getEditable('system.theme')->get('default');
    $themeinfo = \Drupal::service('extension.list.theme')->getExtensionInfo($theme);
    $basetheme = $themeinfo['base theme'] ?? '';
    $config = [];
    $eligible_themes = ['speedway'];
    $is_eligible_theme = in_array($theme, $eligible_themes) || in_array($basetheme, $eligible_themes);

    if ($is_eligible_theme) {
      $config['link']['decorators'][] = [
        'mode' => 'manual',
        'label' => 'Primary button',
        'classes' => ['ut-btn'],
      ];
      $config['link']['decorators'][] = [
        'mode' => 'manual',
        'label' => 'Secondary button',
        'classes' => ['ut-btn--secondary'],
      ];
      $config['link']['decorators'][] = [
        'mode' => 'manual',
        'label' => 'Authentication required icon',
        'classes' => ['ut-cta-link--lock'],
      ];
      $config['link']['decorators'][] = [
        'mode' => 'manual',
        'label' => 'External link icon',
        'classes' => 'ut-cta-link--external',
      ];
      $config['link']['decorators'][] = [
        'mode' => 'manual',
        'label' => 'Right-facing caret',
        'classes' => ['ut-cta-link--angle-right'],
      ];
    }
    $config['link']['decorators'][] = [
      'mode' => 'manual',
      'label' => 'Open in new tab',
      'attributes' => [
        'target' => '_blank',
        'rel' => 'noopener noreferrer',
      ],
    ];
    return $config;
  }

}
