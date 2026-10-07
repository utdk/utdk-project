<?php

namespace Drupal\utexas_instagram\Hook;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\utexas_instagram\InstagramHelper;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    return [
      'utexas_instagram_feed_block' => [
        'variables' => [
          'attributes' => [],
          'instagram_posts' => [],
          'instagram_logo' => '',
          'instagram_handle' => '',
          'arrow_icon' => '',
        ],
      ],
      'utexas_instagram_feed_post' => [
        'variables' => [
          'attributes' => [],
          'pic_link' => '',
          'pic_src' => '',
          'pic_alt' => '',
          'pic_text' => '',
          'username' => '',
        ],
      ],
      'block__block_content__utexas_instagram' => [
        'template' => 'block--block-content--utexas-instagram',
        'base hook' => 'block',
      ],
      'block__inline_block__utexas_instagram' => [
        'template' => 'block--inline-block--utexas-instagram',
        'base hook' => 'block',
      ],
      'field__field_utexas_instagram_headline' => [
        'template' => 'field--field-utexas-instagram-headline',
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    if (isset($variables['content']['#block_content'])) {
      $block = $variables['content']['#block_content'];
      if ($block->bundle() !== 'utexas_instagram') {
        return;
      }
      $config_id = $block->get('field_utexas_instagram_acct')->getString();
      $variables['instagram_posts'] = InstagramHelper::render($config_id);
    }
  }

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(&$variables) {
    if ($variables['element']['#field_name'] !== 'field_utexas_instagram_headline') {
      return;
    }
    // If the Instagram instance has been set to link to the Instagram account,
    // render the headline field as a link.
    if ($block = $variables['element']['#object']) {
      $link_handle = $block->get('field_utexas_instagram_handle')->getValue();
      if ($link_handle && isset($link_handle[0]['value'])) {
        if ($link_handle[0]['value'] !== "1") {
          return;
        }
        $config_id = $block->get('field_utexas_instagram_acct')->getString();
        $variables['username'] = InstagramHelper::getAccountLink($config_id);
        if (!empty($variables['username'])) {
          $url = Url::fromUri('https://www.instagram.com/' . $variables['username']);
          foreach ($variables['items'] as &$item) {
            $text = $item['content']['#context']['value'];
            $linked_text = Link::fromTextAndUrl($text, $url)->toRenderable();
            $linked_text['#attributes']['class'][] = 'ut-cta-link--external';
            $item['content']['#context']['value'] = $linked_text;
          }
        }
      }
    }
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_block_alter')]
  public function themeSuggestionsBlockAlter(array &$suggestions, array $variables) {
    if (isset($variables['elements']['content']['#block_content'])) {
      $block = $variables['elements']['content']['#block_content']->bundle();
      if ($block == "utexas_instagram") {
        $suggestions[] = "block__block_content__utexas_instagram";
      }
    }
  }

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public function cron() {
    Cache::invalidateTags(['utexas_instagram_feed_blocks']);
  }

}
