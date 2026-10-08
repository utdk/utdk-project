<?php

namespace Drupal\utevent_readonly;

/**
 * Business logic for making the feature UI read-only.
 */
class ReadOnlyHelper {

  /**
   * Configuration patterns that should be read-only.
   *
   * @var array
   */
  public static $readOnlyPatterns = [
    'utevent_event',
    'utevent_event_listing',
    'utevent_location',
    'utevent_tags',
    'utevent_listing_block',
    'utevent_listing_page',
    'utevent_calendar_page',
    'utevent_localist',
    'utevent_export',
    'utevent_import_json',
    'utevent_import_xml',
  ];

  /**
   * Field names that should be disabled.
   *
   * @var array
   */
  public static $disabledFields = [
    'name',
    'description',
    'title_label',
    'help',
    'preview_mode',
    'label',
    'revision',
    'cardinality',
    'cardinality_number',
    'cardinality_container',
  ];

  /**
   * Routes that should be viewable but not modifiable.
   *
   * @var array
   */
  public static $viewableRoutes = [
    'entity.node_type.edit_form',
    'entity_form_display',
    'field_ui_fields',
    'entity_view_display',
    'entity.field_config.node_storage_edit_form',
    'entity.field_config.node_field_edit_form',
    'entity.block_content_type.edit_form',
    'entity.field_config.block_field_edit_form',
    'entity.field_config.block_content_storage_edit_form',
    'entity.field_config.block_content_field_edit_form',
    'entity.view.edit_form',
    'entity.taxonomy_vocabulary.edit_form',
  ];

  /**
   * Print a warning message about the feature's read-only status.
   *
   * @param string $id
   *   A machine name that may contain the restricted entity.
   *
   * @return bool
   *   Whether or not the machine name contains the restricted entity.
   */
  public static function matchesReadOnlyPattern($id) {
    foreach (self::$readOnlyPatterns as $entity) {
      if (strpos($id, $entity) !== FALSE) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Print a warning message about the feature's read-only status.
   */
  public static function warn() {
    \Drupal::messenger()->addWarning(t('The Event feature is read-only and may not be changed. Developers who want to customize this should first read <a href="https://utexas.sharepoint.com/sites/UTDK/SitePages/Developers/Developers--What-configuration-can-and-cannot-be-modified.aspx">https://utexas.sharepoint.com/sites/UTDK/SitePages/Developers/Developers--What-configuration-can-and-cannot-be-modified.aspx</a>.'));
  }

  /**
   * Routes which are *candidates* for restriction.
   *
   * @var array
   */
  public static $restrictableRoutes = [
    // Feeds.
    'entity.feeds_feed_type.mapping',
    'entity.feeds_feed_type.sources',
    'entity.feeds_feed_type.source_edit',
    'entity.feeds_feed_type.source_delete',
    'entity.feeds_feed_type.tamper',
    'entity.feeds_feed_type.tamper_add',
    'entity.feeds_feed_type.tamper_edit',
    'entity.feeds_feed_type.tamper_delete',
    // Nodes.
    'entity.entity_form_display.node.default',
    'entity.entity_form_display.node.form_mode',
    'entity.entity_view_display.node.default',
    'entity.entity_view_display.node.view_mode',
    'entity.field_config.node_field_delete_form',
    'entity.field_config.node_field_edit_form',
    'entity.field_config.node_storage_edit_form',
    'entity.node.field_ui_fields',
    'entity.node_type.delete_form',
    'entity.node_type.edit_form',
    'entity.node_type.moderation',
    'entity.scheduled_update_type.add_form.field.node',
    'field_ui.field_storage_config_add_node',
    'field_ui.field_group_add_node.display',
    'field_ui.field_group_add_node.display.view_mode',
    'field_ui.field_group_add_node.form_display',
    'field_ui.field_storage_config_add:field_storage_config_add_node',
    // Block content types.
    'entity.entity_form_display.block_content.default',
    'entity.entity_form_display.block_content.form_mode',
    'entity.entity_view_display.block_content.default',
    'entity.entity_view_display.block_content.view_mode',
    'entity.field_config.block_content_field_delete_form',
    'entity.field_config.block_content_field_edit_form',
    'entity.field_config.block_content_storage_edit_form',
    'entity.block_content.field_ui_fields',
    'entity.block_content_type.delete_form',
    'entity.block_content_type.edit_form',
    'field_ui.field_storage_config_add_block_content',
    'field_ui.field_group_add_block_content.form_display',
    'field_ui.field_group_add_block_content.display',
    'entity.scheduled_update_type.add_form.field.block_content',
    // Taxonomy vocabularies.
    'entity.entity_form_display.taxonomy_term.default',
    'entity.entity_form_display.taxonomy_term.form_mode',
    'entity.entity_view_display.taxonomy_term.default',
    'entity.entity_view_display.taxonomy_term.view_mode',
    'entity.field_config.taxonomy_term_field_delete_form',
    'entity.field_config.taxonomy_term_field_edit_form',
    'entity.field_config.taxonomy_term_storage_edit_form',
    'entity.scheduled_update_type.add_form.field.taxonomy_term',
    'entity.taxonomy_term.field_ui_fields',
    'entity.taxonomy_vocabulary.delete_form',
    'entity.taxonomy_vocabulary.edit_form',
    'field_ui.field_storage_config_add_taxonomy_term',
    'field_ui.field_group_add_taxonomy_term.display',
    'field_ui.field_group_add_taxonomy_term.form_display',
    'field_ui.field_group_add_taxonomy_term.view_mode',
    // Views.
    'entity.view.delete_form',
    'entity.view.edit_display_form',
    'entity.view.edit_form',
  ];

}
