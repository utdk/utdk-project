<?php

namespace Drupal\utevent_import\Plugin\Tamper;

use Drupal\tamper\Attribute\Tamper;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileExists;
use Drupal\media\Entity\Media;
use Drupal\tamper\TamperableItemInterface;
use Drupal\tamper\TamperBase;
use GuzzleHttp\Client;

/**
 * Plugin implementation of the utevent_create_media_tamper plugin.
 */
#[Tamper(
  id: 'utevent_create_media_tamper',
  label: new TranslatableMarkup('Create Media Tamper'),
  description: new TranslatableMarkup('Create a utexas_image media entity from an image URL.'),
  category: new TranslatableMarkup('Other')
)]
class UtEventCreateMedia extends TamperBase {

  /**
   * {@inheritdoc}
   */
  public function tamper($data, ?TamperableItemInterface $item = NULL) {
    if (empty($data) || !UrlHelper::isValid($data, TRUE)) {
      return $data;
    }

    $media_type = 'utexas_image';
    $media_field = 'field_utexas_media_image';
    $items = $item ? $item->getSource() : [];
    $alt = $items['utevent_image_alt_json'] ?? $items['utevent_image_alt_xml'] ?? '';
    $title = $items['utevent_image_title_json'] ?? $items['utevent_image_title_xml'] ?? '';
    $file_name = $this->getFileName($data);

    $file = $this->findFile($file_name);
    if (FALSE === $file) {
      $file = $this->writeData($this->getContent($data), 'public://' . $file_name);
    }

    if (!$file) {
      return $data;
    }

    $media = $this->findMedia($file->id(), $media_field);
    if (!$media) {
      $media = Media::create([
        'name' => $file_name,
        'bundle' => $media_type,
        'uid' => 1,
        'langcode' => 'en',
        'status' => 1,
        $media_field => [
          'target_id' => $file->id(),
          'alt' => $alt,
          'title' => $title,
        ],
      ]);
      $media->save();
    }

    return $media->id();
  }

  /**
   * Derive a safe filename from the source URL.
   *
   * @param string $url
   *   The source URL of the file.
   *
   * @return string
   *   A sanitized filename derived from the URL.
   */
  protected function getFileName(string $url) {
    $filename = trim(basename($url), " \t\n\r\0\x0B.");
    [$filename] = explode('?', $filename);
    return $filename;
  }

  /**
   * Fetch the remote file contents.
   *
   * @param string $url
   *   The source URL of the file.
   *
   * @return string|false
   *   The file contents as a string, or FALSE on failure.
   */
  protected function getContent(string $url) {
    $client = new Client();
    $response = $client->request('GET', $url);
    if ($response->getStatusCode() >= 400) {
      return FALSE;
    }
    return (string) $response->getBody();
  }

  /**
   * Write file data to the public scheme, reusing an existing file if present.
   *
   * @param mixed $data
   *   The file data to write.
   * @param string $destination
   *   The destination URI, e.g. 'public://filename.jpg'.
   *
   * @return \Drupal\file\Entity\File|false
   *   The created file entity or FALSE on failure.
   */
  protected function writeData(mixed $data, string $destination) {
    try {
      return \Drupal::service('file.repository')->writeData($data, $destination, FileExists::Rename);
    }
    catch (EntityStorageException | FileException $e) {
      return FALSE;
    }
  }

  /**
   * Look up an existing managed file by filename.
   *
   * @param string $file_name
   *   The filename to search for.
   *
   * @return \Drupal\file\Entity\File|false
   *   The file entity if found, or FALSE if not found.
   */
  protected function findFile(string $file_name) {
    $existing = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['filename' => $file_name]);
    return $existing ? reset($existing) : FALSE;
  }

  /**
   * Look up an existing media entity referencing the given file id.
   *
   * @param int $fid
   *   The file id to search for.
   * @param string $media_field
   *   The media field to search in.
   *
   * @return \Drupal\media\Entity\Media|false
   *   The media entity if found, or FALSE if not found.
   */
  protected function findMedia(int $fid, string $media_field) {
    $existing = \Drupal::entityTypeManager()
      ->getStorage('media')
      ->loadByProperties([$media_field => $fid]);
    return $existing ? reset($existing) : FALSE;
  }

}
