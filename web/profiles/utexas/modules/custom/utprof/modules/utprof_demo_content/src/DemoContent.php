<?php

namespace Drupal\utprof_demo_content;

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
   * Main function to create curated profile nodes.
   */
  public static function createDemoContent() {
    self::generateTerms();
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('utprof_demo_content')->getPath();
    foreach (array_values(DemoData::loadData()) as $item) {
      self::saveProfileNode($item, $module_path);
    }
  }

  /**
   * Business logic for saving a node.
   *
   * @param array $item
   *   A keyed array of node data by field name.
   * @param string $module_path
   *   The path to the utprof_demo_content module.
   */
  public static function saveProfileNode(array $item, $module_path) {
    $node = Node::create(['type' => 'utprof_profile']);
    $node->set('title', $item['title']);
    $node->set('uid', '1');
    foreach ($item['fields'] as $machine_name => $field_data) {
      $node->set($machine_name, $field_data);
    }
    $group_tids = [];
    foreach ($item['profile_groups'] as $group_name) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
        ->loadByProperties(['name' => $group_name, 'vid' => 'utprof_groups']);
      $group = reset($term);
      $group_tids[] = $group->id();
    }
    $node->set('field_utprof_profile_groups', $group_tids);
    if (isset($item['basic_media'])) {
      $image_metadata = $item['basic_media'];
      $image_metadata['filepath'] = $module_path . '/assets/' . $image_metadata['asset'];
      $media_id = self::createMediaItem($image_metadata);
      $node->set('field_utprof_basic_media', $media_id);
    }
    $node->status = 1;
    $node->enforceIsNew();
    $node->save();
  }

  /**
   * Create terms to use when creating nodes.
   */
  public static function generateTerms() {
    $profile_groups = ['Leadership', 'Faculty', 'Staff'];
    $weight = 0;
    foreach ($profile_groups as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utprof_groups',
        'weight' => $weight,
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
