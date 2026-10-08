<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits;

use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\Language;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;

/**
 * General-purpose methods for interacting with Drupal entities.
 */
trait EntityTestTrait {

  /**
   * Copy files from utexas/tests/fixtures to public://.
   *
   * @return array
   *   Returns array of copied files (filename => realPath).
   */
  protected function copyTestFiles() {
    /** @var \Drupal\Core\Extension\ProfileExtensionList $profile_extension_list */
    $profile_extension_list = \Drupal::service('extension.list.profile');

    $target_uri = 'public://test_files/';
    $profile_test_fixtures_path = $profile_extension_list->getPath('utexas') . '/tests/fixtures/';

    return $this->copyAssetFiles($target_uri, $profile_test_fixtures_path);
  }

  /**
   * Copy files from utexas/tests/fixtures to public://.
   *
   * @return array
   *   Returns array of copied files (filename => realPath).
   */
  protected function copySiteAnnouncementIconFiles() {
    /** @var \Drupal\Core\Extension\ModuleExtensionList $module_extension_list */
    $module_extension_list = $this->container->get('extension.list.module');

    $target_uri = 'public://announcement_icons/';
    $module_assets_path = $module_extension_list->getPath('utexas_site_announcement') . '/assets/';

    return $this->copyAssetFiles($target_uri, $module_assets_path);
  }

  /**
   * Copy files from utexas_block_social_links/icons to public://social_icons/.
   *
   * @return array
   *   Returns array of copied files (filename => realPath).
   */
  protected function copySocialLinksIconFiles() {
    /** @var \Drupal\Core\Extension\ModuleExtensionList $module_extension_list */
    $module_extension_list = $this->container->get('extension.list.module');

    $target_uri = 'public://social_icons/';
    $module_assets_path = $module_extension_list->getPath('utexas_block_social_links') . '/icons/';

    return $this->copyAssetFiles($target_uri, $module_assets_path);
  }

  /**
   * Copy assest from an extension directory.
   *
   * @param string $target_uri
   *   The uri destination for the files.
   * @param string $assets_path
   *   The path of the extension assets.
   *
   * @return array
   *   Returns array of copied files (filename => realPath).
   */
  protected function copyAssetFiles($target_uri, $assets_path) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');

    $file_system->prepareDirectory($target_uri, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $files = $file_system->scanDirectory($assets_path, '/.*/', ['key' => 'name'], 0);

    foreach ($files as $file) {
      $source_uri = $file->uri;
      $desination_uri = $target_uri . $file->filename;
      $file_system->copy($source_uri, $desination_uri, FileExists::Rename);

      $copied_files[$file->filename] = $file_system->realpath($desination_uri);
    }

    return $copied_files ?? [];
  }

  /**
   * Creates a test image file in Drupal and returns the file entity ID.
   *
   * @param string|null $file_name
   *   An optional filename from a file located in tests/fixtures.
   *
   * @return array
   *   The file entity id and entity name.
   */
  protected function createTestFileImage($file_name = NULL) {
    /** @var \Drupal\file\FileStorageInterface $file_storage */
    $file_storage = \Drupal::service('entity_type.manager')->getStorage('file');

    // If file entity already exists, return that info. Do not create it again.
    /** @var \Drupal\file\FileInterface $file_entity */
    if ($file_name && $file_entity = $file_storage->loadByProperties(['filename' => $file_name])) {
      return [
        'id' => $file_entity->id(),
        'name' => $file_entity->getFilename(),
      ];
    }

    /** @var object $test_image_files */
    $test_image_files = $this->getTestFiles('image');

    // Find file_name in test_image_files list.
    foreach ($test_image_files as $test_file) {
      if ($test_file->filename === $file_name) {
        $uri = $test_file->uri;
      }
    }

    // Create a file entity image file. If the filename is not found above,
    // use image test file[0].
    $file = File::create([
      'uri' => $uri ?? $test_image_files[0]->uri,
      'uid' => 0,
      'status' => FileInterface::STATUS_PERMANENT,
    ]);

    $file->save();

    return [
      'id' => $file->id(),
      'name' => $file->getFilename(),
    ];
  }

  /**
   * Creates a test image in Drupal and returns the media MID.
   *
   * @param string|null $file_name
   *   An optional filename from a file located in tests/fixtures.
   *
   * @return string
   *   The MID.
   */
  protected function createTestMediaImage($file_name = NULL) {
    /** @var \Drupal\media\MediaStorage $media_storage */
    $media_storage = \Drupal::service('entity_type.manager')->getStorage('media');

    // If media entity for image file already exists, return that id. Do not
    // create it again.
    /** @var \Drupal\media\MediaInterface $image_media */
    if ($file_name && $image_media = $media_storage->loadByProperties(['name' => $file_name])) {
      return $image_media->id();
    }

    $file_info = $this->createTestFileImage($file_name);

    $image_media = $media_storage->create([
      'name' => $file_info['name'],
      'bundle' => 'utexas_image',
      'uid' => '1',
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'status' => '1',
      'field_utexas_media_image' => [
        'target_id' => $file_info['id'],
        'alt' => $this->t('@media_name Alt Text', ['@media_name' => $file_info['name']]),
        'title' => $this->t('@media_name Title Text', ['@media_name' => $file_info['name']]),
      ],
    ]);
    $image_media->save();
    return $image_media->id();
  }

  /**
   * Creates a test video in Drupal and returns the media MID.
   *
   * @return string
   *   The MID.
   */
  protected function createTestMediaVideoExternal() {

    $video_media = Media::create([
      'name' => 'Video 1',
      'bundle' => 'utexas_video_external',
      'uid' => '1',
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'status' => '1',
      'field_media_oembed_video' => [
        'value' => "https://www.youtube.com/watch?v=vXyqBkXDacU",
      ],
    ]);
    $video_media->save();
    return $video_media->id();
  }

  /**
   * Populates & saves a basic page to the database.
   *
   * @return int
   *   The new node's internal ID.
   */
  protected function createBasicPage() {
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Test Basic Page',
    ]);
    $node->save();
    return $node->id();
  }

  /**
   * Populates & saves a utexas_flex_page to the database.
   *
   * @return int
   *   The new node's internal ID.
   */
  protected function createFlexPage() {
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    return $node->id();
  }

  /**
   * Remove node entities.
   *
   * @param array $node_ids
   *   An array of node ids to delete.
   */
  protected function removeNodes(array $node_ids) {
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple($node_ids);
    $storage_handler->delete($entities);
  }

  /**
   * Remove block content entities.
   *
   * @param array $block_descriptions
   *   An array of blocks to delete, by description field value.
   */
  protected function removeBlocks(array $block_descriptions) {
    $storage_handler = \Drupal::entityTypeManager()->getStorage("block_content");
    foreach ($block_descriptions as $block_description) {
      $entity_ids[] = $this->drupalGetBlockByInfo($block_description)->id();
    }
    $entities = $storage_handler->loadMultiple($entity_ids);
    $storage_handler->delete($entities);
  }

  /**
   * Get a custom block from the database based on its title.
   *
   * @param string $info
   *   A block title, usually generated by $this->randomMachineName().
   * @param bool $reset
   *   (optional) Whether to reset the entity cache.
   *
   * @return \Drupal\block\BlockInterface
   *   A block entity matching $info.
   */
  protected function drupalGetBlockByInfo($info, $reset = FALSE) {
    if ($reset) {
      \Drupal::entityTypeManager()->getStorage('block_content')->resetCache();
    }
    $blocks = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => $info]);
    // Get the first block returned from the database.
    $returned_block = reset($blocks);
    return $returned_block;
  }

}
