<?php

namespace Drupal\utexas_block_social_links\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\utexas_block_social_links\Services\UTexasSocialLinkOptions;

/**
 * Plugin implementation of the 'utexas_social_link_field' field type.
 */
#[FieldType(
  id: 'utexas_social_link_field',
  label: new TranslatableMarkup('UTexas Social Link'),
  description: new TranslatableMarkup('Defines a tuple field with social icon selector & URL entry'),
  default_widget: 'utexas_social_link_widget',
  default_formatter: 'utexas_social_link_formatter',
  no_ui: TRUE
)]
class UTexasSocialLinkField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using TranslatableMarkup class directly.
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setSetting('case_sensitive', TRUE);
    $properties['icon_size'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Icon Size'))
      ->setSetting('case_sensitive', TRUE);
    $properties['social_account_links'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Social Links Data'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(TRUE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'headline' => [
          'type' => 'varchar_ascii',
          'length' => 255,
          'binary' => TRUE,
        ],
        'icon_size' => [
          'type' => 'varchar_ascii',
          'length' => 255,
          'binary' => TRUE,
        ],
        'social_account_links' => [
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
    $options = UTexasSocialLinkOptions::getOptionsArray();
    $values['headline'] = $random->word(3);
    $values['icon_size'] = 'medium';
    $values['social_account_links'] = serialize([
      [
        'social_account_name' => array_rand($options),
        'social_account_url' => 'https://' . $random->word(5) . '.com',
      ],
    ]);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('social_account_links')->getValue();
    return $value === NULL || $value === '' || empty($value);
  }

}
