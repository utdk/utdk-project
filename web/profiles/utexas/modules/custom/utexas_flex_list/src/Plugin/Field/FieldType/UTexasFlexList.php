<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'utexas_flex_list' field type.
 */
#[FieldType(
  id: 'utexas_flex_list',
  label: new TranslatableMarkup('Flex list'),
  description: new TranslatableMarkup('Heading + content fields, displayable in a variety of formats'),
  default_widget: 'utexas_flex_list',
  default_formatter: 'utexas_flex_list_default'
)]
class UTexasFlexList extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['header'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Header'))
      ->setRequired(FALSE);
    $properties['content_value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Content value'))
      ->setRequired(FALSE);
    $properties['content_format'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Content format'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'header' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
        'content_value' => [
          'type' => 'text',
          'size' => 'normal',
          'binary' => FALSE,
        ],
        'content_format' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['header'] = "Header";
    $values['content_value'] = 'Lorem ipsum <a href="https://drupalkit.its.utexas.edu">dolor sit</a> amet.';
    $values['content_format'] = 'flex_html';
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $header = $this->get('header')->getValue();
    $body = $this->get('content_value')->getValue();
    return empty($body) && empty($header);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $manager = $this->getTypedDataManager()->getValidationConstraintManager();

    $constraints[] = $manager->create('ComplexData', [
      'header' => [
        'NotBlank' => [
          'message' => $this->t('This value should not be blank when %content_value_label has a value.', [
            '%content_value_label' => 'content',
          ]),
        ],
      ],
    ]);

    return $constraints;
  }

}
