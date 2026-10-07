<?php

namespace Drupal\utevent_demo_content;

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
   * Main function to create curated event nodes.
   */
  public static function createDemoContent() {
    $event_tags = ['Demo Tag 1', 'Demo Tag 2'];
    $weight = 0;
    foreach ($event_tags as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utevent_tags',
        'weight' => $weight,
      ])->save();
      $weight++;
    }
    $event_locations = ['Demo Location 1', 'Demo Location 2'];
    $weight = 0;
    foreach ($event_locations as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utevent_location',
        'weight' => $weight,
      ])->save();
      $weight++;
    }
    $media_id = self::createMediaItem();
    foreach (array_values(DemoData::loadData()) as $item) {
      self::saveEventNode($item, $media_id);
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
  public static function saveEventNode(array $item, $media_id) {
    $node = Node::create(['type' => 'utevent_event']);
    $node->set('title', $item['title']);
    $node->set('uid', '1');
    $simple_fields = [
      'field_utevent_body',
      'field_utevent_featured',
      'field_utevent_status',
      'field_utevent_datetime',
    ];
    foreach ($simple_fields as $field) {
      if (isset($item[$field])) {
        $node->set($field, $item[$field]);
      }
    }
    $taxonomy_fields = ['field_utevent_location', 'field_utevent_tags'];
    foreach ($taxonomy_fields as $field) {
      $tids = [];
      if (!isset($item[$field])) {
        continue;
      }
      foreach ($item[$field] as $name) {
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
          ->loadByProperties([
            'name' => $name,
            'vid' => str_replace('field_', '', $field),
          ]);
        $object = reset($term);
        $tids[] = $object->id();
      }
      $node->set($field, $tids);
    }
    if ($item['media']) {
      $node->set('field_utevent_main_media', $media_id);
    }
    $node->status = 1;
    $node->enforceIsNew();
    $node->save();
  }

  /**
   * Create a single Drupal media entity.
   *
   * @return int
   *   The MID of a single Drupal Media entity.
   */
  public static function createMediaItem() {
    /** @var \Drupal\file\FileRepositoryInterface $file_repository */
    $file_repository = \Drupal::service('file.repository');
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('utevent_demo_content')->getPath();
    $image_metadata = [
      'filepath' => $module_path . '/assets/texas-shield-150x150.png',
      'filename' => 'Texas Shield',
      'alt_text' => 'The official shield of the University of Texas',
      'title_text' => 'The official shield of the University of Texas',
    ];
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
