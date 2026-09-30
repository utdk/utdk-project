<?php

namespace Drupal\utevent_block_type_event_listing\Hook;

use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utevent_block_type_event_listing\EventListingHelper;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_block_alter')]
  public function themeSuggestionsBlockAlter(array &$suggestions, array $variables) {
    // Add a template suggestion for the utevent_event_listing bundle.
    if (isset($variables['elements']['content']['#block_content'])) {
      $theme_hook_original = $variables['theme_hook_original'];
      $base_plugin_id = $variables['elements']['#base_plugin_id'];
      $bundle = $variables['elements']['content']['#block_content']->bundle();
      // Theme suggestions for custom inline blocks are already correctly added
      // by core, so we do not want to add another one here.
      if ($bundle === 'utevent_event_listing' && $base_plugin_id !== 'inline_block') {
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
      'block__block_content__utevent_event_listing' => [
        'base hook' => 'block',
      ],
      'block__inline_block__utevent_event_listing' => [
        'base hook' => 'block',
      ],
    ];
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    // Add a rendered View of the event listings matching criteria specified
    // from the block's fields.
    $content = $variables['elements']['content'];
    if (isset($content['#block_content']) && $content['#block_content'] instanceof BlockContentInterface) {
      if ($content['#block_content']->bundle() === 'utevent_event_listing') {
        $variables['cta'] = EventListingHelper::generateCta($content['#block_content']);

        if ($listing = EventListingHelper::buildContextualView($content['#block_content'])) {
          $variables['listing'] = $listing;
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_views_view_fields().
   */
  #[Hook('preprocess_views_view_fields')]
  public function preprocessViewsViewFields(&$variables) {
    $view = $variables['view'];
    if ($view->id() !== 'utevent_listing_block') {
      return;
    }

    if (!isset($variables['fields']['field_utevent_datetime_1'])) {
      return;
    }

    // Strip tags to get raw rendered value.
    $variables['fields']['field_utevent_datetime_1']->content = strip_tags($variables['fields']['field_utevent_datetime_1']->content);

    // Do not display datetime narrative for non-recurring events.
    /** @var \Drupal\views\Plugin\views\field\EntityField $field_handler */
    $field_handler = $variables['fields']['field_utevent_datetime_1']->handler;
    $field_items = $field_handler->getItems($variables['row']);
    $field_item = reset($field_items);
    /** @var \Drupal\smart_date\Plugin\Field\FieldType\SmartDateItem $smart_date_item */
    $smart_date_item = $field_item['raw'];
    $smart_date_item_values = $smart_date_item->getValue();
    if ($smart_date_item_values['rrule'] === NULL) {
      unset($variables['fields']['field_utevent_datetime_1']);
    }
  }

  /**
   * Implements hook_plugin_filter_TYPE__CONSUMER_alter().
   */
  #[Hook('plugin_filter_block__layout_builder_alter')]
  public function pluginFilterBlockLayoutBuilderAlter(array &$definitions) {
    // The Event Listing view block is not intended to be placed on its own,
    // but rather via the Event listing block type.
    unset($definitions['views_block:utevent_listing_block-block_1']);
  }

}
