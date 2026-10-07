<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldFormatter;

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
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 */
#[FieldFormatter(
  id: 'utexas_promo_unit',
  label: new TranslatableMarkup('Landscape (220x140, 11:7 ratio)'),
  field_types: ['utexas_promo_unit']
)]
class UTexasPromoUnitDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

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
    $cache_tags = $this->generateCacheTags('utexas_responsive_image_pu_landscape');
    foreach ($items as $delta => $item) {
      $instances = [];
      $promo_unit_items = !empty($item->promo_unit_items) ? unserialize($item->promo_unit_items) : [];
      if (!empty($promo_unit_items)) {
        foreach ($promo_unit_items as $key => $instance) {
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
          $format = $instance_item['copy']['format'] ?? 'flex_html';
          $copy = $instance_item['copy']['value'] ?? '';
          $instances[$key]['copy'] = [
            '#type' => 'processed_text',
            '#text' => $copy,
            '#format' => $format,
            '#langcode' => $item->getLangcode(),
          ];
          // Show link below title and/or copy field only if there is link text.
          if ((!empty($instance_item['link']['uri'])) && (!empty($instance_item['link']['title']))) {
            $link_item = [
              'link' => $instance_item['link'],
            ];
            if (!empty($instance_item['headline'])) {
              // Make the link URL non-tab-able if a headline is present.
              $link_item['link']['options']['attributes']['tabindex'] = '-1';
              $link_item['link']['options']['attributes']['aria-hidden'] = 'true';
            }
            $instances[$key]['link'] = UtexasLinkOptionsHelper::buildLink($link_item, ['ut-link--darker']);
          }
          if (!empty($instance_item['image'])) {
            $image = $instance_item['image'] ?? FALSE;
            $responsive_image_style_name = 'utexas_responsive_image_pu_landscape';
            $instances[$key]['image'] = $this->generateImageRenderArray($image, $responsive_image_style_name, $cache_tags);
          }
        }
      }
      $elements[$delta] = [
        '#theme' => 'utexas_promo_unit',
        '#headline' => RenderElementHelper::filterSingleLineText($item->headline, TRUE),
        '#promo_unit_items' => $instances,
        '#image_display' => 'landscape-image',
      ];
    }
    return $elements;

  }

  /**
   * Helper method to generate cache tags.
   */
  protected function generateCacheTags($responsive_image_style_name) {
    // Collect cache tags to be added for each item in the field.
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
    return $cache_tags;
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
          '#attributes' => [],
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
