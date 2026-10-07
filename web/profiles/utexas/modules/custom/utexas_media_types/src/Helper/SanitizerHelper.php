<?php

namespace Drupal\utexas_media_types\Helper;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use enshrined\svgSanitize\Sanitizer;
use Psr\Log\LoggerAwareTrait;

/**
 * Sanitizer Helper class.
 *
 * @package Drupal\utexas_media_types\Helper
 *
 * @internal
 */
class SanitizerHelper {

  use LoggerAwareTrait;

  /**
   * The Drupal file system helper.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The sanitizer.
   *
   * @var \enshrined\svgSanitize\Sanitizer
   */
  private $sanitizer;

  /**
   * SanitizerHelper constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The Drupal file system helper.
   * @param \enshrined\svgSanitize\Sanitizer $sanitizer
   *   The sanitizer.
   */
  public function __construct(FileSystemInterface $file_system, Sanitizer $sanitizer) {
    $this->fileSystem = $file_system;
    $this->sanitizer = $sanitizer;
  }

  /**
   * Sanitize a File entity.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file.
   *
   * @return bool
   *   TRUE if the sanitization has succeeded. FALSE otherwise.
   *
   * @throws \Exception
   */
  public function sanitize(FileInterface $file) {
    if ('image/svg+xml' !== $file->getMimeType()) {
      return FALSE;
    }

    $filePath = $this->fileSystem->realpath($file->getFileUri());
    if (FALSE === $filePath) {
      $this->logger->notice(sprintf('Could not resolve the path of the file (URI: "%s").', $file->getFileUri()));
      return FALSE;
    }

    if (FALSE === file_exists($filePath)) {
      $this->logger->notice(sprintf('The file does not exist (path: "%s").', $filePath));
      return FALSE;
    }

    $fileContent = file_get_contents($filePath);
    if (FALSE === $fileContent || empty($fileContent)) {
      $this->logger->notice(sprintf('Could not retrieve the content of the file (path: "%s").', $filePath));
      return FALSE;
    }

    $fileCleanContent = $this->sanitizer->sanitize($fileContent);

    if (FALSE === file_put_contents($filePath, $fileCleanContent)) {
      throw new \Exception(sprintf('Cannot sanitize the file (URI: "%s")', $file->getFileUri()));
    }

    return TRUE;
  }

}
