<?php

namespace Drupal\utexas_resources\Plugin\Field\FieldType;

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
 * Plugin implementation of the 'utexas_resources' field type.
 */
#[FieldType(
  id: 'utexas_resources',
  label: new TranslatableMarkup('Resources'),
  description: new TranslatableMarkup('Headline, image, unlimited links'),
  default_widget: 'utexas_resources',
  default_formatter: 'utexas_resources'
)]
class UTexasResources extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['resource_items'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Resource Items'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'headline' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'resource_items' => [
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
    $values['headline'] = ucfirst($random->word(mt_rand(5, 10)));
    for ($i = 0; $i < 2; $i++) {
      $values['resource_items'][$i]['item']['headline'] = ucfirst($random->word(mt_rand(5, 10)));

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
      $values['resource_items'][$i]['item']['image'] = $image_media->id();

      // // Set of possible top-level domains for sample link value.
      $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
      // // Set random length for the domain name.
      $domain_length = mt_rand(7, 15);
      $values['resource_items'][$i]['item']['links'][0]['uri'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
      $values['resource_items'][$i]['item']['links'][0]['title'] = ucfirst($random->word(mt_rand(5, 10)));
      $values['resource_items'][$i]['item']['links'][1]['uri'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
      $values['resource_items'][$i]['item']['links'][1]['title'] = ucfirst($random->word(mt_rand(5, 10)));
    }
    $values['resource_items'] = serialize($values['resource_items']);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $headline = $this->get('headline')->getValue();
    $resource_items = $this->get('resource_items')->getValue();
    return empty($headline) && empty($resource_items);
  }

}
