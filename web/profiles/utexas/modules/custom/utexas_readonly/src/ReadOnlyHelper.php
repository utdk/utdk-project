<?php

namespace Drupal\utexas_readonly;

/**
 * Business logic for making the feature UI read-only.
 */
class ReadOnlyHelper {

  /**
   * Restricted node types.
   *
   * @var array
   */
  public static $restrictedConfig = [
    'flex_html',
  ];

  /**
   * Restricted patterns.
   *
   * @var array
   */
  public static $restrictedPatterns = [
    'utexas_image_style',
    'utexas_responsive',
  ];

  /**
   * Restricted fields.
   *
   * @var array
   */
  public static $restrictedFields = [
    'field_block_fca',
    'field_block_featured_highlight',
    'field_block_hero',
    'field_block_il',
    'field_block_pca',
    'field_block_pl',
    'field_block_pu',
    'field_block_ql',
    'field_block_resources',
    'field_media_file',
    'field_media_oembed_video',
    'field_flex_page_metatags',
    'field_utexas_sl_social_links',
    'field_utexas_call_to_action_link',
    'field_utexas_flex_list_items',
    'field_utexas_media_image',
    'field_utexas_instagram_acct',
    'layout_builder__layout',
  ];

  /**
   * Restricted media types.
   *
   * @var array
   */
  public static $restrictedMediaTypes = [
    'utexas_image',
    'utexas_video_external',
    'utexas_document',
  ];

  /**
   * Restricted node types.
   *
   * @var array
   */
  public static $restrictedNodeTypes = [
    'utexas_flex_page',
  ];

  /**
   * Restricted block types.
   *
   * @var array
   */
  public static $restrictedBlockTypes = [
    'call_to_action',
    'utexas_featured_highlight',
    'utexas_flex_content_area',
    'utexas_flex_list',
    'utexas_hero',
    'utexas_image_link',
    'utexas_instagram',
    'utexas_photo_content_area',
    'utexas_promo_list',
    'utexas_promo_unit',
    'utexas_quick_links',
    'utexas_resources',
    'social_links',
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
    'entity.block_content_type.edit_form',
    'entity.field_config.node_storage_edit_form',
    'entity.field_config.node_field_edit_form',
    'entity.field_config.block_field_edit_form',
    'entity.field_config.block_content_storage_edit_form',
    'entity.field_config.block_content_field_edit_form',
    'entity.node_type.edit_form',
    'entity.view.edit_form',
    'entity.taxonomy_vocabulary.edit_form',
    'entity_view_display',
    'entity_form_display',
    'field_ui_fields',
  ];

  /**
   * Print a warning message about the feature read-only status.
   */
  public static function warn() {
    \Drupal::messenger()->addWarning(t('This component is read-only and should not be modified.'));
  }

  /**
   * Routes which are *candidates* for restriction.
   *
   * @var array
   */
  public static $restrictableRoutes = [
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
    // Image styles.
    'image.effect_add_form',
    'image.effect_delete',
    'image.effect_edit_form',
    // Media entities.
    'field_ui.field_storage_config_add_media',
    // Views.
    'entity.view.delete_form',
    'entity.view.edit_display_form',
    'entity.view.edit_form',
  ];

}
