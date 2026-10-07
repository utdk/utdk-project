<?php

namespace Drupal\utnews_demo_content;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\Language;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Generate curated demo content.
 */
class DemoContent {

  /**
   * Main function to create curated news nodes.
   */
  public static function createDemoContent() {
    // Create terms for referencing in nodes.
    self::generateTerms();
    // Create media item for article main image.
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('utnews_demo_content')->getPath();
    $image_metadata = [
      'filepath' => $module_path . '/assets/tower-lighting.gif',
      'filename' => 'Tower lighting',
      'alt_text' => 'Animated gif of the UT Tower',
      'title_text' => 'Animated gif of the UT Tower',
    ];
    $media_id = self::createMediaItem($image_metadata);
    foreach (array_values(DemoData::loadData()) as $item) {
      self::saveNewsNode($item, $media_id);
    }
  }

  /**
   * Business logic for saving a node.
   *
   * @param array $item
   *   A keyed array of node data by field name.
   * @param int $media_id
   *   A Media Entity ID for use in Media fields.
   */
  public static function saveNewsNode(array $item, $media_id) {
    $node = Node::create(['type' => 'utnews_news']);
    $node->set('title', $item['title']);
    $node->set('uid', '1');
    $simple_fields = [
      'field_utnews_body',
      'field_utnews_featured',
      'field_utnews_publication_date',
      'field_utnews_external_link',
    ];
    foreach ($simple_fields as $field) {
      if (isset($item[$field])) {
        $node->set($field, $item[$field]);
      }
    }
    $taxonomy_fields = [
      'field_utnews_news_categories',
      'field_utnews_news_tags',
      'field_utnews_article_author',
    ];
    $taxonomy_vocabularies = [
      'utnews_categories',
      'utnews_tags',
      'utnews_authors',
    ];
    foreach ($taxonomy_fields as $delta => $field) {
      $tids = [];
      if (!isset($item[$field])) {
        continue;
      }
      foreach ($item[$field] as $name) {
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
          ->loadByProperties([
            'name' => $name,
            'vid' => $taxonomy_vocabularies[$delta],
          ]);
        $object = reset($term);
        $tids[] = $object->id();
      }
      $node->set($field, $tids);
    }
    if (isset($item['field_utnews_display_image']['value']) && $item['field_utnews_display_image']['value'] === TRUE) {
      $node->set('field_utnews_main_media', $media_id);
    }
    $node->status = 1;
    $node->enforceIsNew();
    $node->save();
  }

  /**
   * Create terms to use when creating nodes.
   */
  public static function generateTerms() {
    $news_tags = ['Demo Tag 1', 'Demo Tag 2'];
    $weight = 0;
    foreach ($news_tags as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utnews_tags',
        'weight' => $weight,
      ])->save();
      $weight++;
    }
    $news_categories = [
      'Demo Category 1',
      'Demo Category 2',
      'General',
      'Opinion',
      'Press Releases',
    ];
    $weight = 0;
    foreach ($news_categories as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utnews_categories',
        'weight' => $weight,
      ])->save();
      $weight++;
    }
    $news_author = ['Demo Author 1', 'Demo Author 2'];
    $weight = 0;
    // Create media item for news author image.
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('utnews_demo_content')->getPath();
    $image_metadata = [
      'filepath' => $module_path . '/assets/generic-headshot.png',
      'filename' => 'Generic headshot',
      'alt_text' => 'Generic avatar image',
      'title_text' => 'Generic avatar image',
    ];
    $media_id = self::createMediaItem($image_metadata);
    foreach ($news_author as $term) {
      $term_media_id = ($term === 'Demo Author 1') ? $media_id : [];
      Term::create([
        'name' => $term,
        'vid' => 'utnews_authors',
        'weight' => $weight,
        'field_utnews_author_image' => $term_media_id,
      ])->save();
      $weight++;
    }
  }

  /**
   * Create a single Drupal media entity.
   *
   * @param array $image_metadata
   *   Metadata for media item.
   *
   * @return int
   *   The MID of a single Drupal Media entity.
   */
  public static function createMediaItem(array $image_metadata) {
    /** @var \Drupal\file\FileRepositoryInterface $file_repository */
    $file_repository = \Drupal::service('file.repository');
    $file_system = \Drupal::service('file_system');
    $filedir = 'public://demo_content/';
    $file_system->prepareDirectory($filedir, FileSystemInterface::CREATE_DIRECTORY);
    $image = File::create();
    $image->setFileUri($image_metadata['filepath']);
    $image->setOwnerId(\Drupal::currentUser()->id());
    $image->setMimeType(\Drupal::service('file.mime_type.guesser')->guessMimeType($image_metadata['filepath']));
    $image->setFileName(basename($image_metadata['filepath']));
    $destination_dir = 'public://generated_sample';
    $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY);
    $destination = $destination_dir . '/' . basename($image_metadata['filepath']);
    $file = $file_repository->copy($image, $destination);
    $image_media = Media::create([
      'name' => $image_metadata['filename'],
      'bundle' => 'utexas_image',
      'uid' => '1',
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'status' => '1',
      'field_utexas_media_image' => [
        'target_id' => $file->id(),
        'alt' => $image_metadata['alt_text'],
        'title' => $image_metadata['title_text'],
      ],
    ]);
    $image_media->save();
    return $image_media->id();
  }

}
