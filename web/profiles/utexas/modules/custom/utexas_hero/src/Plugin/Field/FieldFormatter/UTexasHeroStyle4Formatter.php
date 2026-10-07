<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_form_elements\RenderElementHelper;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 */
#[FieldFormatter(
  id: 'utexas_hero_4',
  label: new TranslatableMarkup('Style 4: Centered image with dark bottom pane containing heading, subheading and call-to-action'),
  field_types: ['utexas_hero']
)]
class UTexasHeroStyle4Formatter extends UTexasHeroFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $responsive_image_style_name = 'utexas_responsive_image_hi';
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
      $cta_item['link']['uri'] = $item->link_uri;
      $cta_item['link']['title'] = $item->link_title ?? NULL;
      $cta_item['link']['options'] = $item->link_options ?? [];
      $cta = UtexasLinkOptionsHelper::buildLink($cta_item, ['ut-btn']);
      $image_render_array = [];
      if ($media = $this->entityTypeManager->getStorage('media')->load($item->media)) {
        $media_attributes = MediaEntityImageHelper::getFileFieldValue($media);
        if ($file = $this->entityTypeManager->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $image = new \stdClass();
          $image->title = NULL;
          $image->alt = $media_attributes[0]['alt'];
          $image->entity = $file;
          $image->uri = $file->getFileUri();
          $image->width = NULL;
          $image->height = NULL;
          // Check if image styles have been disabled (e.g., animated GIF)
          if (!$item->disable_image_styles) {
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
          else {
            $image_render_array = [
              '#theme' => 'image',
              '#uri' => $image->uri,
              '#alt' => $image->alt,
            ];
          }
        }
        $this->renderer->addCacheableDependency($image_render_array, $file);
        $this->renderer->addCacheableDependency($image_render_array, $media);
        if (MediaEntityImageHelper::mediaIsRestricted($media)) {
          $image_render_array = [];
        }
      }
      $elements[] = [
        '#theme' => 'utexas_hero_4',
        '#media' => $image_render_array,
        '#heading' => RenderElementHelper::filterSingleLineText($item->heading, TRUE),
        '#subheading' => RenderElementHelper::filterSingleLineText($item->subheading, TRUE),
        '#cta' => $cta,
      ];
      if ($delta === 0) {
        $image_style = $this->entityTypeManager->getStorage('image_style')->load('utexas_image_style_1140w_616h');
        $image_style_path = $image_style->buildUrl($image->uri);
        $preload = [
          'rel' => 'preload',
          'as' => 'image',
          'href' => $image_style_path,
        ];
        $elements['#attached']['html_head_link'][] = [$preload];
      }
    }
    return $elements;
  }

}
