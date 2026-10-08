<?php

namespace Drupal\utexas_media_types\Helper;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * File helper class.
 *
 * @package Drupal\utexas_media_types\Helper
 *
 * @internal
 */
class FileHelper {

  use LoggerAwareTrait;

  /**
   * The Drupal file system helper.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * FileHelper constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The Drupal file system helper.
   */
  public function __construct(FileSystemInterface $file_system) {
    $this->fileSystem = $file_system;
  }

  /**
   * Try to update the saved size of a file.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file.
   *
   * @return bool
   *   TRUE if the size was successfully updated, FALSE otherwise.
   */
  public function updateSize(FileInterface $file) {
    $file_path = $this->fileSystem->realpath($file->getFileUri());
    if (FALSE === $file_path) {
      $this->logger->error(sprintf('Could not resolve the path of the file (URI: "%s").', $file->getFileUri()));

      return FALSE;
    }

    $size = @filesize($file_path);
    if (FALSE === $size) {
      $this->logger->error(sprintf('Could not get the file size (path: "%s").', $file_path));

      return FALSE;
    }

    $file->setSize($size);
    try {
      $file->save();
    }
    catch (EntityStorageException $e) {
      $this->logger->error(sprintf('Could not save the file (fid: "%s", path: "%s").', $file->id(), $file_path));
    }

    return TRUE;
  }

}
