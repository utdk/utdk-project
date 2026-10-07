<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\RenderElementHelper;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;
use Drupal\utexas_media_types\MediaEntityImageHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'utexas_promo_list' formatter.
 */
#[FieldFormatter(
  id: 'utexas_promo_list',
  label: new TranslatableMarkup('Single list full (1 item per row)'),
  field_types: ['utexas_promo_list']
)]
class UTexasPromoListDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $responsive_image_style_name = 'utexas_responsive_image_pl';
    // Collect cache tags.
    $responsive_image_style = $this->entityTypeManager->getStorage('responsive_image_style')->load($responsive_image_style_name);
    $image_styles_to_load = [];
    $cache_tags = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }
    $image_styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple($image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }
    foreach ($items as $item) {
      $instances = [];
      $promo_list_items = is_string($item->promo_list_items) ? unserialize($item->promo_list_items) : $item->promo_list_items;
      if (!empty($promo_list_items)) {
        foreach ($promo_list_items as $key => $instance) {
          $instance_item = $instance['item'];
          if (!empty($instance_item['headline'])) {
            $instances[$key]['headline'] = $instance_item['headline'];
            // Convert the headline to a link, if present.
            if (!empty($instance_item['link']['uri'])) {
              $instances[$key]['headline'] = UtexasLinkOptionsHelper::buildLink($instance_item, ['ut-link--darker'], $instance_item['headline']);
            }
            else {
              $instances[$key]['headline'] = RenderElementHelper::filterSingleLineText($instance_item['headline'], TRUE);
            }
          }
          if (!empty($instance_item['copy']['value'])) {
            $instances[$key]['copy'] = [
              '#type' => 'processed_text',
              '#text' => $instance_item['copy']['value'],
              '#format' => $instance_item['copy']['format'],
              '#langcode' => $item->getLangcode(),
            ];
          }
          if (!empty($instance_item['link']['uri'])) {
            $instances[$key]['link'] = UtexasLinkOptionsHelper::buildLink($instance_item, ['ut-link--darker']);
          }
          if (!empty($instance_item['image'])) {
            $image = $instance_item['image'] ?? FALSE;
            $instances[$key]['image'] = $this->generateImageRenderArray($image, $responsive_image_style_name, $cache_tags);
          }
        }
      }
      $elements[] = [
        '#theme' => 'utexas_promo_list',
        '#headline' => RenderElementHelper::filterSingleLineText($item->headline, TRUE),
        '#promo_list_items' => $instances,
        '#wrapper' => '',
      ];
    }
    return $elements;
  }

  /**
   * Helper method to prepare image array.
   */
  protected function generateImageRenderArray($image, $responsive_image_style_name, $cache_tags) {
    // Initialize image render array as false in case that images are not found.
    $image_render_array = FALSE;
    if (!empty($image) && $media = $this->entityTypeManager->getStorage('media')->load($image)) {
      $media_attributes = MediaEntityImageHelper::getFileFieldValue($media);
      $image_render_array = [];
      if ($file = $this->entityTypeManager->getStorage('file')->load($media_attributes[0]['target_id'])) {
        $image = new \stdClass();
        $image->title = NULL;
        $image->alt = $media_attributes[0]['alt'];
        $image->entity = $file;
        $image->uri = $file->getFileUri();
        $image->width = NULL;
        $image->height = NULL;
        $image_render_array = [
          '#theme' => 'responsive_image_formatter',
          '#item' => $image,
          '#attributes' => ['class' => ['ut-img--fluid']],
          '#responsive_image_style_id' => $responsive_image_style_name,
          '#cache' => [
            'tags' => $cache_tags,
          ],
        ];
      }
      if (MediaEntityImageHelper::mediaIsRestricted($media)) {
        $image_render_array = [];
      }
      // Add the file entity to the cache dependencies.
      // This will clear our cache when this entity updates.
      $this->renderer->addCacheableDependency($image_render_array, $file);
      $this->renderer->addCacheableDependency($image_render_array, $media);
    }
    return $image_render_array;
  }

}
