<?php

namespace Drupal\utexas_photo_content_area\Plugin\Field\FieldType;

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
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Plugin implementation of the 'utexas_photo_content_area' field type.
 */
#[FieldType(
  id: 'utexas_photo_content_area',
  label: new TranslatableMarkup('Photo Content Area'),
  description: new TranslatableMarkup('A field with headline, media, date, copy, & link.'),
  default_widget: 'utexas_photo_content_area',
  default_formatter: 'utexas_photo_content_area'
)]
class UTexasPhotoContentArea extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['image'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Image'))
      ->setRequired(FALSE);
    $properties['photo_credit'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Photo credit'))
      ->setRequired(FALSE);
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['copy_value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Copy value'))
      ->setRequired(FALSE);
    $properties['copy_format'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Copy format'))
      ->setRequired(FALSE);
    $properties['links'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Links'))
      ->setRequired(FALSE);
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
        'photo_credit' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'headline' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'copy_value' => [
          'type' => 'text',
          'size' => 'normal',
          'binary' => FALSE,
        ],
        'copy_format' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'links' => [
          'type' => 'blob',
          'size' => 'normal',
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
    $values['photo_credit'] = $random->sentences(1);
    $values['headline'] = $random->word(10);
    $values['copy_value'] = $random->sentences(8);
    $values['copy_format'] = 'restricted_html';
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
    $values['links'] = serialize([
      ['uri' => 'https://utexas.edu', 'title' => 'UT Homepage'],
      ['uri' => 'https://news.utexas.edu', 'title' => 'UT News'],
    ]);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $media = $this->get('image')->getValue();
    $photo_credit = $this->get('photo_credit')->getValue();
    $headline = $this->get('headline')->getValue();
    $copy = $this->get('copy_value')->getValue();
    $links = $this->get('links')->getValue();
    return empty($media) && empty($headline) && empty($photo_credit) && empty($copy) && empty($links);
  }

}
