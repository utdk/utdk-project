<?php

namespace Drupal\utexas_flex_content_area\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\media\IFrameUrlHelper;
use Drupal\media\MediaInterface;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Drupal\utexas_form_elements\RenderElementHelper;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;
use Drupal\utexas_media_types\MediaEntityImageHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'utexas_flex_content_area' formatter.
 */
#[FieldFormatter(
  id: 'utexas_flex_content_area',
  label: new TranslatableMarkup('Display 2 items per row'),
  field_types: ['utexas_flex_content_area']
)]
class UTexasFlexContentAreaDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The iFrame URL helper service.
   *
   * @var \Drupal\media\IFrameUrlHelper
   */
  protected $iFrameUrlHelper;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The oEmbed resource fetcher.
   *
   * @var \Drupal\media\OEmbed\ResourceFetcherInterface
   */
  protected $resourceFetcher;

  /**
   * The oEmbed URL resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

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
   * @param \Drupal\media\IFrameUrlHelper $iframe_url_helper
   *   The iFrame URL helper service.
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resource_fetcher
   *   The oEmbed resource fetcher service.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $url_resolver
   *   The oEmbed URL resolver service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, IFrameUrlHelper $iframe_url_helper, ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->iFrameUrlHelper = $iframe_url_helper;
    $this->resourceFetcher = $resource_fetcher;
    $this->urlResolver = $url_resolver;
    $this->logger = $logger_factory->get('media');
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
      $container->get('renderer'),
      $container->get('media.oembed.iframe_url_helper'),
      $container->get('media.oembed.resource_fetcher'),
      $container->get('media.oembed.url_resolver'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [
      '#items' => [],
    ];
    $responsive_image_style_name = 'utexas_responsive_image_fca';
    // Collect cache tags to be added for each item in the field.
    /** @var Drupal\responsive_image\Entity\ResponsiveImageStyle $responsive_image_style */
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
    foreach ($items as $delta => $item) {
      $cta = "";
      $media_ratio = "";
      // Format links.
      $links = unserialize($item->links ?? '', ['allowed_classes' => FALSE]) ?: [];
      foreach ($links as $key => $link) {
        $link['link'] = $link;
        $links[$key] = UtexasLinkOptionsHelper::buildLink($link, ['ut-link']);
      }
      // Format CTA.
      $cta_item = $item->getValue();
      $cta_item['link']['title'] = $cta_item['link_text'] ?? '';
      $cta_item['link']['uri'] = $cta_item['link_uri'];
      $cta_item['link']['options'] = $cta_item['link_options'];
      // Format headline.
      $headline = $item->headline ?? '';
      if (!empty($cta_item['link_uri']) && !empty($item->headline)) {
        // Convert the headline to a link, if present.
        $headline = UtexasLinkOptionsHelper::buildLink($cta_item, ['ut-link--darker'], $item->headline);
      }
      elseif (empty($cta_item['link_uri']) && !empty($item->headline)) {
        $headline = RenderElementHelper::filterSingleLineText($headline, TRUE);
      }
      if ($headline) {
        // Make the CTA non-tab-able if a headline is present.
        $cta_item['link']['options']['attributes']['tabindex'] = '-1';
        $cta_item['link']['options']['attributes']['aria-hidden'] = 'true';
      }
      if (!empty($cta_item['link']['title'])) {
        $cta = UtexasLinkOptionsHelper::buildLink($cta_item, ['ut-btn']);
      }
      $media_render_array = [];
      if ($media = $this->entityTypeManager->getStorage('media')->load($item->image)) {
        switch ($media->bundle()) {
          case 'utexas_restricted_image':
          case 'utexas_image':
            $media_render_array = $this->generateImageRenderArray($media, $responsive_image_style_name);
            break;

          case 'utexas_video_external':
            $media_render_array = $this->generateVideoRenderArray($media);
            if (!empty($media_render_array)) {
              $media_ratio = number_format($media_render_array['#height'] / $media_render_array['#width'], 2);
            }
            break;
        }
      }
      $format = $item->copy_format ?? 'flex_html';
      $copy = $item->copy_value ?? '';
      $elements[$delta] = [
        '#theme' => 'utexas_flex_content_area',
        '#media' => $media_render_array,
        '#headline' => $headline,
        '#copy' => [
          '#type' => 'processed_text',
          '#text' => $copy,
          '#format' => $format,
          '#langcode' => $item->getLangcode(),
        ],
        '#media_ratio' => $media_ratio,
        '#links' => $links,
        '#cta' => $cta,
      ];

      // Add class to the item.attributes object.
      $elements['#items'][$delta] = new \stdClass();
      $elements['#items'][$delta]->_attributes['class'][] = 'ut-flex-content-area';
    }
    $elements['#attributes']['class'][] = 'ut-flex-content-area-wrapper';
    $elements['#attached']['library'][] = 'utexas_flex_content_area/flex-content-area';
    return $elements;

  }

  /**
   * Prepare a video render array.
   *
   * @param \Drupal\media\MediaInterface $media
   *   A Drupal media entity object.
   *
   * @return string[]
   *   A video render array.
   */
  private function generateVideoRenderArray(MediaInterface $media) {
    // The logic of this is largely based on
    // Drupal\media\Plugin\Field\FieldFormatter\OembedFormatter.
    $media_render_array = [];
    $field_media_oembed_video = $media->get('field_media_oembed_video')->getValue();
    $value = $field_media_oembed_video[0]['value'];
    // These can be hardcoded, if we prefer to constrain the iframe display.
    $max_width = 330;
    $max_height = 0;

    try {
      $resource_url = $this->urlResolver->getResourceUrl($value, $max_width, $max_height);
      $resource = $this->resourceFetcher->fetchResource($resource_url);
      $max_width = $resource->getWidth();
      $max_height = $resource->getHeight();
    }
    catch (ResourceException $exception) {
      $this->logger->error("Could not retrieve the remote URL (@url).", ['@url' => $value]);
    }

    if (empty($value) || empty($resource)) {
      return $media_render_array;
    }

    $url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => $value,
        'max_width' => $max_width,
        'max_height' => $max_height,
        'hash' => $this->iFrameUrlHelper->getHash($value, $max_width, $max_height),
      ],
    ]);

    // Render videos and rich content in an iframe for security reasons.
    // @see: https://oembed.com/#section3
    $media_render_array = [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#attributes' => [
        'src' => $url->toString(),
        'frameborder' => 0,
        'scrolling' => FALSE,
        'allowtransparency' => TRUE,
        'width' => "100%",
        'height' => "100%",
        'title' => $resource->getProvider()->getName() . ' content: ' . $resource->getTitle(),
      ],
      '#width' => $max_width,
      '#height' => $max_height,
    ];

    // Add the media entity to the cache dependencies.
    // This will clear our cache when this entity updates.
    $this->renderer->addCacheableDependency($media_render_array, $media);
    return $media_render_array;
  }

  /**
   * Prepare an image render array.
   *
   * @param \Drupal\media\MediaInterface $media
   *   A Drupal media entity object.
   * @param string $responsive_image_style_name
   *   The machine name of a responsive image style.
   *
   * @return string[]
   *   An image render array.
   */
  private function generateImageRenderArray(MediaInterface $media, $responsive_image_style_name) {
    $media_render_array = [];
    $media_attributes = MediaEntityImageHelper::getFileFieldValue($media);
    /** @var Drupal\file\Entity\File $file */
    if ($file = $this->entityTypeManager->getStorage('file')->load($media_attributes[0]['target_id'])) {
      $image = new \stdClass();
      $image->title = NULL;
      $image->alt = $media_attributes[0]['alt'];
      $image->entity = $file;
      $image->uri = $file->getFileUri();
      $image->width = NULL;
      $image->height = NULL;
      $media_render_array = [
        '#theme' => 'responsive_image_formatter',
        '#item' => $image,
        '#attributes' => [
          'class' => 'ut-img--fluid',
        ],
        '#responsive_image_style_id' => $responsive_image_style_name,
        '#cache' => [
          'tags' => $this->generateImageCacheTags($responsive_image_style_name),
        ],
      ];
      // Add the file entity to the cache dependencies.
      // This will clear our cache when this entity updates.
      $this->renderer->addCacheableDependency($media_render_array, $file);
      $this->renderer->addCacheableDependency($media_render_array, $media);
    }
    if (MediaEntityImageHelper::mediaIsRestricted($media)) {
      return [];
    }
    return $media_render_array;
  }

  /**
   * Prepare cache tags.
   *
   * @param string $responsive_image_style_name
   *   The machine name of a responsive image style.
   *
   * @return string[]
   *   An cache tag array.
   */
  private function generateImageCacheTags($responsive_image_style_name) {
    // Collect cache tags to be added for each item in the field.
    /** @var Drupal\responsive_image\Entity\ResponsiveImageStyle $responsive_image_style */
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

}
