<?php

namespace Drupal\utnews_block_type_news_listing\Hook;

use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\utnews_block_type_news_listing\NewsListingHelper;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_block_alter')]
  public function themeSuggestionsBlockAlter(array &$suggestions, array $variables) {
    // Add a template suggestion for the utnews_article_listing bundle.
    if (isset($variables['elements']['content']['#block_content'])) {
      $theme_hook_original = $variables['theme_hook_original'];
      $base_plugin_id = $variables['elements']['#base_plugin_id'];
      $bundle = $variables['elements']['content']['#block_content']->bundle();
      // Theme suggestions for custom inline blocks are already correctly added
      // by core, so we do not want to add another one here.
      if ($bundle === 'utnews_article_listing' && $base_plugin_id !== 'inline_block') {
        // Add a bundle-specific theme suggestion.
        array_splice($suggestions, 1, 0, $theme_hook_original . '__' . $base_plugin_id . '__' . $bundle);
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    // Register the templates defined in /templates.
    return [
      'block__block_content__utnews_article_listing' => [
        'base hook' => 'block',
      ],
      'block__inline_block__utnews_article_listing' => [
        'base hook' => 'block',
      ],
    ];
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    // Add a rendered View of the news listings matching criteria specified
    // from the block's fields.
    $content = $variables['elements']['content'];
    if (isset($content['#block_content']) && $content['#block_content'] instanceof BlockContentInterface) {
      if ($content['#block_content']->bundle() === 'utnews_article_listing') {
        $variables['cta'] = NewsListingHelper::generateCta($content['#block_content']);

        if ($listing = NewsListingHelper::buildContextualView($content['#block_content'])) {
          $variables['listing'] = $listing;
        }
      }
    }
  }

  /**
   * Implements hook_plugin_filter_TYPE__CONSUMER_alter().
   */
  #[Hook('plugin_filter_block__layout_builder_alter')]
  public function pluginFilterBlockLayoutBuilderAlter(array &$definitions) {
    // The News Listing view block is not intended to be placed on its own,
    // but rather via the News listing block type.
    unset($definitions['views_block:utnews_listing_block-teaser']);
    unset($definitions['views_block:utnews_listing_block-utnews_date_image']);
    unset($definitions['views_block:utnews_listing_block-utnews_image']);
    unset($definitions['views_block:utnews_listing_block-utnews_summary']);
    unset($definitions['views_block:utnews_listing_block-utnews_date']);
    unset($definitions['views_block:utnews_listing_block-utnews_summary_image']);
    unset($definitions['views_block:utnews_listing_block-utnews_summary_date']);
    unset($definitions['views_block:utnews_listing_block-utnews_summary_image_date']);
  }

}
