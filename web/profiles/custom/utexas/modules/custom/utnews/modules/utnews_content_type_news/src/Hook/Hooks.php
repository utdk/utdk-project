<?php

namespace Drupal\utnews_content_type_news\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utnews_content_type_news\NewsContentTypeHelper;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'node__utnews_news__full' => [
        'template' => 'node--utnews-news--full',
        'base hook' => 'node',
      ],
      'node__utnews_news' => [
        'template' => 'node--utnews-news',
        'base hook' => 'node',
      ],
    ];
  }

  /**
   * Implements hook_preprocess_node().
   */
  #[Hook('preprocess_node')]
  public function preprocessNode(&$variables) {
    $node = $variables['elements']['#node'];
    $type = $node->getType();

    if ($type !== 'utnews_news') {
      return;
    }

    $variables['has_sidebar_fields'] = NewsContentTypeHelper::hasSidebarFields($node);
    $variables['author_information'] = NewsContentTypeHelper::prepareAuthorInformation($node);
    $variables['main_image'] = NewsContentTypeHelper::prepareMainImage($node);
    $variables['news_tags'] = NewsContentTypeHelper::prepareNewsTaxonomy($node, 'field_utnews_news_tags', 'tags');
    $variables['news_categories'] = NewsContentTypeHelper::prepareNewsTaxonomy($node, 'field_utnews_news_categories', 'category');
    $variables['linked_title'] = NewsContentTypeHelper::prepareLinkedTitle($node);
    $body_field = $node->get('field_utnews_body')->getValue();
    $variables['body_summary'] = $body_field[0]['summary'] ?? '';

    // The Views field handler `NewsListing` can be applied to all view modes
    // and allows for optionally suppressing 3 fields, listed below, based on
    // the settings values indicated.
    $dynamic_display = [
      '#display_date' => 'field_utnews_publication_date',
      '#display_summary' => 'field_utnews_body',
      '#display_thumbnail' => 'field_utnews_main_media',
    ];
    foreach ($dynamic_display as $setting => $field) {
      if (isset($variables['elements'][$setting]) && $variables['elements'][$setting] === FALSE) {
        unset($variables['content'][$field]);
      }
    }
  }

  /**
   * Helper function to add the "Standard workflow" to the content type.
   */
  public static function addStandardWorkflow() {
    $config_id = 'workflows.workflow.standard_workflow';
    $config = \Drupal::service('config.factory')->getEditable($config_id);
    if (is_null($config->get('id'))) {
      \Drupal::logger('utnews_content_type_news')->notice('Standard workflow not found. Skipping...');
      // This site does not use the standard_workflow. Move on.
      return;
    }
    \Drupal::logger('utnews_content_type_news')->notice('Standard workflow found. Updating...');
    $type_settings = $config->get('type_settings');
    $new_type_settings = $type_settings;
    $new_type_settings['entity_types']['node'][] = 'utnews_news';
    $config->set('type_settings', $new_type_settings);
    $config->save();
    // A cache rebuild is required for this configuration change to show.
    drupal_flush_all_caches();
  }

}
