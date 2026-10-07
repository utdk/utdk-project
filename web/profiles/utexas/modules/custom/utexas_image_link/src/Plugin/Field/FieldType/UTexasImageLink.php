<?php

namespace Drupal\utexas_image_link\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Plugin implementation of the 'utexas_image_link' field type.
 */
#[FieldType(
  id: 'utexas_image_link',
  label: new TranslatableMarkup('Image Link'),
  description: new TranslatableMarkup('Linked image'),
  default_widget: 'utexas_image_link',
  default_formatter: 'utexas_image_link'
)]
class UTexasImageLink extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['image'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Image'))
      ->setRequired(TRUE);
    $properties['link'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link'))
      ->setRequired(FALSE);
    $properties['link_text'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link Text'))
      ->setRequired(FALSE);
    $properties['link_options'] = MapDataDefinition::create()
      ->setLabel(t('Link Options'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'image' => [
          'type' => 'int',
        ],
        'link' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'link_text' => [
          'description' => 'The link text.',
          'type' => 'varchar',
          'length' => 255,
        ],
        'link_options' => [
          'description' => 'Serialized array of options for the link.',
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();

    // Attributes for sample image.
    static $images = [];

    $min_resolution = '100x100';
    $max_resolution = '600x600';
    $extensions = ['png', 'gif', 'jpg', 'jpeg'];
    $extension = array_rand(array_combine($extensions, $extensions));
    // Generate a max of 5 different images.
    if (!isset($images[$extension][$min_resolution][$max_resolution]) || count($images[$extension][$min_resolution][$max_resolution]) <= 5) {
      /** @var \Drupal\Core\File\FileSystemInterface $file_system */
      $file_system = \Drupal::service('file_system');
      $tmp_file = $file_system->tempnam('temporary://', 'generateImage_');
      $destination = $tmp_file . '.' . $extension;
      try {
        $file_system->move($tmp_file, $destination);
      }
      catch (FileException $e) {
        // Ignore failed move.
      }
      if ($path = $random->image($file_system->realpath($destination), $min_resolution, $max_resolution)) {
        /** @var \Drupal\file\FileRepositoryInterface $file_repository */
        $file_repository = \Drupal::service('file.repository');
        $image = File::create();
        $image->setFileUri($path);
        $image->setOwnerId(\Drupal::currentUser()->id());
        $image->setMimeType(\Drupal::service('file.mime_type.guesser')->guessMimeType($path));
        $image->setFileName(basename($path));
        $destination_dir = 'public://generated_sample';
        $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY);
        $destination = $destination_dir . '/' . basename($path);
        $file = $file_repository->move($image, $destination);
        $images[$extension][$min_resolution][$max_resolution][$file->id()] = $file;
      }
      else {
        return [];
      }
    }
    else {
      // Select one of the images we've already generated for this field.
      $image_index = array_rand($images[$extension][$min_resolution][$max_resolution]);
      $file = $images[$extension][$min_resolution][$max_resolution][$image_index];
    }
    $image_media = Media::create([
      'name' => 'Image 1',
      'bundle' => 'utexas_image',
      'uid' => '1',
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'status' => '1',
      'field_utexas_media_image' => [
        'target_id' => $file->id(),
        'alt' => t('Test Alt Text'),
        'title' => t('Test Title Text'),
      ],
    ]);
    $image_media->save();
    $values['image'] = $image_media->id();

    // Set of possible top-level domains for sample link value.
    $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
    // Set random length for the domain name.
    $domain_length = mt_rand(7, 15);
    // We only need a URL here.
    $values['link'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $image = $this->get('image')->getValue();
    $link = $this->get('link')->getValue();
    return empty($image) && empty($link);
  }

}
