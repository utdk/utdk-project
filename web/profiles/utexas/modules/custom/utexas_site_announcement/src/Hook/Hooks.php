<?php

namespace Drupal\utexas_site_announcement\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'utexas_site_announcement' => [
        'variables' => [
          'title' => NULL,
          'header_id' => NULL,
          'icon' => NULL,
          'message' => NULL,
          'cta' => NULL,
          'background_color' => NULL,
          'text_color' => NULL,
          'unique_id' => NULL,
        ],
        'template' => 'utexas-site-announcement',
        'initial preprocess' => static::class . ':preprocessSiteAnnouncement',
      ],
    ];
  }

  /**
   * Implements template_preprocess_site_announcement().
   */
  #[Hook('preprocess_site_announcement')]
  public function preprocessSiteAnnouncement(array &$variables): void {
    $url = Url::fromRoute('utexas_announcement.configuration');
    if (\Drupal::currentUser()->hasPermission('manage utexas site announcement')) {
      $link = Link::fromTextAndUrl($this->t('Update this announcement'), $url);
      $link = $link->toRenderable();
      $link['#attributes'] = [
        'class' => [
          'ut-btn--secondary',
        ],
      ];
      $variables['edit_link'] = $link;
    }
  }

}
