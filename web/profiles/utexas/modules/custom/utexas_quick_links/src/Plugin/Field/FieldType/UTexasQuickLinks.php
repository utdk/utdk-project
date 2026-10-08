<?php

namespace Drupal\utexas_quick_links\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'utexas_quick_links' field type.
 */
#[FieldType(
  id: 'utexas_quick_links',
  label: new TranslatableMarkup('Quick Links'),
  description: new TranslatableMarkup('Unlimited links, with optional copy text'),
  default_widget: 'utexas_quick_links',
  default_formatter: 'utexas_quick_links'
)]
class UTexasQuickLinks extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['copy_value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Copy format'))
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
    $values['headline'] = $random->word(mt_rand(1, 255));
    $values['copy'] = $random->sentences(mt_rand(1, 2));
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
    $headline = $this->get('headline')->getValue();
    $copy = $this->get('copy_value')->getValue();
    $links = $this->get('links')->getValue();
    return ($headline === NULL || $headline === '') &&
      ($copy === NULL || $copy === '') &&
      ($links === NULL || $links === '');
  }

}
