<?php

namespace Drupal\utexas_hero_carousel\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_hero\Plugin\Field\FieldWidget\UTexasHeroWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'utexas_hero' widget.
 */
#[FieldWidget(
  id: 'utexas_hero_Carousel',
  label: new TranslatableMarkup('Hero Carousel'),
  field_types: ['utexas_hero']
)]
class HeroCarouselWidget extends UTexasHeroWidget {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *   The entity type manager service.
   */
  protected $entityTypeManager;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManager $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Get the form item that this widget is being applied to.
    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];

    // Add details wrapper for UX.
    // Add these elements before '#type' so that we can use ::children().
    foreach (Element::children($element) as $key) {
      $element['hero_carousel'][$key] = $element[$key];
      unset($element[$key]);
    }
    // Add element wrapper type.
    $element['hero_carousel']['#type'] = 'details';
    $element['hero_carousel']['media']['#description'] = $this->t('Upload an image with a minimum resolution of 2280px X 900px (a 2.5:1 ratio). Images have a displayed height limitation of 543px.');
    // Get media entity name.
    if ($item->media) {
      /** @var \Drupal\media\MediaStorage $media_storage */
      $media_storage = $this->entityTypeManager->getStorage('media');
      /** @var \Drupal\media\MediaInterface $media_entity */
      $media_entity = $media_storage->load($item->media);
      if ($media_entity && $media_entity->hasField('name')) {
        $media_name = $media_entity->get('name')->getString();
      }
    }
    $media_name = $media_name ?? NULL;
    // Add element wrapper title.
    $element['hero_carousel']['#title'] = $this->t('Hero Item %number %headline', [
      '%number' => $delta + 1,
      '%headline' => $media_name ? '(' . $media_name . ')' : '',
    ]);

    $element['#attached']['library'][] = 'utexas_hero_carousel/widget';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Remove details wrapper to allow original processing.
    foreach ($values as &$value) {
      $value = array_merge($value, $value['hero_carousel']);
      unset($value['hero_carousel']);
    }

    return parent::massageFormValues($values, $form, $form_state);
  }

}
