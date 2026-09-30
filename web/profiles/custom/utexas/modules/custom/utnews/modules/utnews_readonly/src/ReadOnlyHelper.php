<?php

namespace Drupal\utnews_readonly;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeTypeInterface;

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
    'utnews',
    'utnews_news',
    'utnews_listing_page',
    'utnews_listing_block',
    'utnews_categories',
    'utnews_authors',
    'utnews_tags',
    'utnews_article_listing',
    'field_utnews',
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
    'entity.taxonomy_vocabulary.overview_form',
  ];

  /**
   * Print a warning message about the feature read-only status.
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
   * Derive a relevant ID based on the type of the route available.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   A Drupal route object.
   *
   * @return mixed
   *   An id or FALSE.
   */
  public static function getId(RouteMatchInterface $route) {
    $id = FALSE;
    if ($view = $route->getParameter('view')) {
      return $view->id();
    }
    elseif ($bundle = $route->getParameter('bundle')) {
      return $bundle;
    }
    elseif ($node_type = $route->getParameter('node_type')) {
      if (is_string($node_type)) {
        return $node_type;
      }
      if ($node_type instanceof NodeTypeInterface) {
        return $node_type->id();
      }
    }
    elseif ($block_type = $route->getParameter('block_content_type')) {
      return $block_type->id();
    }
    elseif ($vocab = $route->getParameter('taxonomy_vocabulary')) {
      return $vocab->id();
    }
    return $id;
  }

  /**
   * Print a warning message about the feature read-only status.
   */
  public static function warn() {
    \Drupal::messenger()->addWarning(t('The News feature is read-only and may not be changed. Developers who want to customize this feature should first read <a href="https://utexas.sharepoint.com/sites/UTDK/SitePages/Developers/Developers--What-configuration-can-and-cannot-be-modified.aspx">https://utexas.sharepoint.com/sites/UTDK/SitePages/Developers/Developers--What-configuration-can-and-cannot-be-modified.aspx</a>.'));
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
    'entity.taxonomy_vocabulary.overview_form',
    'field_ui.field_storage_config_add_taxonomy_term',
    'field_ui.field_group_add_taxonomy_term.display',
    'field_ui.field_group_add_taxonomy_term.form_display',
    'field_ui.field_group_add_taxonomy_term.view_mode',
    // Views.
    'entity.view.delete_form',
    'entity.view.edit_display_form',
    'entity.view.edit_form',
  ];

  /**
   * These paths should.
   *
   * @var array
   */
  public static $readOnlyPaths = [
    '/admin/structure/types/manage/utnews_news/fields',
    '/admin/structure/types/manage/utnews_news/form-display',
    '/admin/structure/types/manage/utnews_news/display',
    '/admin/structure/block/block-content/manage/utnews_article_listing/fields',
    '/admin/structure/block/block-content/manage/utnews_article_listing/form-display',
    '/admin/structure/block/block-content/manage/utnews_article_listing/display',
    '/admin/structure/taxonomy/manage/utnews_authors/overview/fields',
    '/admin/structure/taxonomy/manage/utnews_authors/overview/form-display',
    '/admin/structure/taxonomy/manage/utnews_authors/overview/display',
    '/admin/structure/taxonomy/manage/utnews_categories/overview/fields',
    '/admin/structure/taxonomy/manage/utnews_categories/overview/form-display',
    '/admin/structure/taxonomy/manage/utnews_categories/overview/display',
    '/admin/structure/taxonomy/manage/utnews_tags/overview/fields',
    '/admin/structure/taxonomy/manage/utnews_tags/overview/form-display',
    '/admin/structure/taxonomy/manage/utnews_tags/overview/display',
  ];

}
