<?php

namespace Drupal\utevent_block_type_event_listing;

use Drupal\block_content\Entity\BlockContent;
use Drupal\views\Views;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;

/**
 * Business logic for rendering the listing view.
 */
class EventListingHelper {

  /**
   * Create a link back to the Event URI.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   The block content.
   *
   * @return array
   *   A renderable Drupal link.
   */
  public static function generateCta(BlockContent $block_content) {
    $cta = '';
    $cta_item = [];
    if ($block_content->hasField('field_utevent_display_cta')) {
      $display_cta = $block_content->get('field_utevent_display_cta')->getValue();
      if ($display_cta[0]['value']) {
        $cta_item['link']['uri'] = 'internal:/events';
        $cta_item['link']['title'] = 'View all Events';
        $cta_item['link']['options'] = [];
        $cta = UtexasLinkOptionsHelper::buildLink($cta_item, ['ut-btn']);
      }
    }
    return $cta;
  }

  /**
   * Inspect block fields & return Views-type filter array.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   The block object.
   *
   * @return array
   *   A Views-type options array.
   */
  public static function generateFilters(BlockContent $block_content) {
    $user_defined_filters['field_utevent_location_target_id'] = [];
    $user_defined_filters['field_utevent_tags_target_id'] = [];

    if ($block_content->hasField('field_utevent_event_location')) {
      $groups = $block_content->get('field_utevent_event_location')->getValue();
      foreach ($groups as $group) {
        $target_id = $group['target_id'];
        if (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($target_id)) {
          $user_defined_filters['field_utevent_location_target_id'][] = $target_id;
        }
      }
    }
    if ($block_content->hasField('field_utevent_event_tags')) {
      $groups = $block_content->get('field_utevent_event_tags')->getValue();
      foreach ($groups as $group) {
        $target_id = $group['target_id'];
        if (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($target_id)) {
          $user_defined_filters['field_utevent_tags_target_id'][] = $target_id;
        }
      }
    }
    return $user_defined_filters;
  }

  /**
   * Generate a renderable View based on user input.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   The block object.
   *
   * @return array
   *   A render array.
   */
  public static function buildContextualView(BlockContent $block_content) {
    $event_listing_view_display = 'block_1';
    $user_defined_filters = self::generateFilters($block_content);
    $view = Views::getView('utevent_listing_block');
    if (is_object($view)) {
      // Specify which Views display to use.
      $view->setDisplay($event_listing_view_display);
      // Dynamically set summary & thumbnail displays from block fields.
      $overrideOptions = FALSE;
      $fields = $view->display_handler->getOption('fields');
      if ($block_content->hasField('field_utevent_display_summaries') &&
        (int) $block_content->get('field_utevent_display_summaries')->getValue()[0]['value'] === 0) {
        unset($fields['field_utevent_body']);
        $overrideOptions = TRUE;
      }
      if ($block_content->hasField('field_utevent_display_thumbnails') &&
        (int) $block_content->get('field_utevent_display_thumbnails')->getValue()[0]['value'] === 0) {
        unset($fields['field_utevent_main_media']);
        $overrideOptions = TRUE;
      }
      if ($overrideOptions) {
        $view->display_handler->overrideOption('fields', $fields);
      }
      // Set filters based on user-provided values.
      $filters = $view->display_handler->getOption('filters');
      foreach ($user_defined_filters as $field => $values) {
        if (isset($filters[$field])) {
          // Reset filters initially.
          $filters[$field]['value'] = [];
          foreach ($values as $value) {
            // Incrementally restrict filters ("OR" logic).
            $filters[$field]['value'][$value] = $value;
          }
        }
      }
      // Handle Featured field differently, as it has a different structure.
      if ($block_content->hasField('field_utevent_limit_featured')) {
        $featured_state = $block_content->get('field_utevent_limit_featured')->getValue()[0]['value'];
        if ($featured_state === 'all') {
          unset($filters['field_utevent_featured_value']);
        }
        elseif ($featured_state === 'featured') {
          $filters['field_utevent_featured_value']['value'] = TRUE;
        }
        elseif ($featured_state === 'not-featured') {
          $filters['field_utevent_featured_value']['value'] = FALSE;
        }
      }
      $view->display_handler->overrideOption('filters', $filters);

      // Set user-defined number of results.
      if ($block_content->hasField('field_utevent_display_count')) {
        $view->setItemsPerPage($block_content->get('field_utevent_display_count')->getValue()[0]['value']);
      }

      return $view->preview($event_listing_view_display);
    }
    return FALSE;
  }

}
