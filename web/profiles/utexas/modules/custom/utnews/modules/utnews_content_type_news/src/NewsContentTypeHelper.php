<?php

namespace Drupal\utnews_content_type_news;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\block\Entity\Block;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;

/**
 * Business logic for rendering the content type.
 */
class NewsContentTypeHelper {

  const UTNEWS_CONTENT_TYPE_NEWS_SIDEBAR_METADATA_FIELDS = [
    'field_utnews_news_categories',
    'field_utnews_news_tags',
  ];

  /**
   * Whether or not there are sidebar fields.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return bool
   *   Whether or not there are contact info fields.
   */
  public static function hasSidebarFields(Node $node) {
    $has_fields = FALSE;
    foreach (self::UTNEWS_CONTENT_TYPE_NEWS_SIDEBAR_METADATA_FIELDS as $meta_field) {
      if ($node->hasField($meta_field) && !$node->$meta_field->isEmpty()) {
        $has_fields = TRUE;
        break;
      }
    }
    return $has_fields;
  }

  /**
   * Provide an array of renderable authoring info, or empty.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   A 3-part array of renderable authoring info, or empty.
   */
  public static function prepareAuthorInformation(Node $node) {
    $author_field = 'field_utnews_article_author';
    if (!$node->hasField($author_field) || $node->get($author_field)->isEmpty()) {
      return FALSE;
    }
    $tid = $node->get($author_field)->getString();
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    if ($term) {
      $url = Url::fromUri('internal:/news?author=' . $term->id());
      $title = $term->getName();
      $authoring_information = [
        'name' => Link::fromTextAndUrl($title, $url),
        'description' => ['#markup' => $term->getDescription()],
        'image' => self::prepareAuthorImage($term),
      ];
      return $authoring_information;
    }
    return [];
  }

  /**
   * A simple array of matching taxonomy terms.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   * @param string $field
   *   The field that provides the taxonomy term reference.
   * @param string $vocab
   *   Vocabulary identifier associated with this reference.
   *
   * @return array
   *   A simple array of matching taxonomy terms.
   */
  public static function prepareNewsTaxonomy(Node $node, $field, $vocab) {
    $output = [];
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return $output;
    }
    $values = $node->get($field)->getValue();
    foreach ($values as $value) {
      /** @var \Drupal\taxonomy\Entity\term */
      if ($term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($value['target_id'])) {
        $url = Url::fromUri('internal:/news?' . $vocab . '=' . $term->id());
        $title = $term->getName();
        $output[] = Link::fromTextAndUrl($title, $url);
      }
    }
    return $output;
  }

  /**
   * A styled main image.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return mixed
   *   A renderable image or FALSE.
   */
  public static function prepareMainImage(Node $node) {
    // Check if image should be displayed.
    $display_field = 'field_utnews_display_image';
    if (!$node->hasField($display_field) || $node->$display_field->getString() === "0") {
      return FALSE;
    }

    // Check for media field.
    $media_field = 'field_utnews_main_media';
    if (!$node->hasField($media_field) || $node->$media_field->isEmpty()) {
      return FALSE;
    }

    // Get media item id.
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $field_items_list */
    $field_items_list = $node->get($media_field);
    $media_id = $field_items_list->getString();

    // Determine image style.
    // 1x Desktop: 1140x616 pixels (1.85:1 ratio throughout).
    $image_style = 'utexas_responsive_image_hi';

    // Create render array.
    /** @var \Drupal\media\MediaStorage $media_storage */
    $media_storage = \Drupal::entityTypeManager()->getStorage('media');
    /** @var \Drupal\media\MediaInterface $media */
    if ($media = $media_storage->load($media_id)) {
      /** @var  \Drupal\media\MediaSourceInterface $media_source */
      $media_source = $media->getSource();
      $media_source_field = $media_source->getConfiguration()['source_field'];
      if ($media->get($media_source_field)->isEmpty()) {
        return FALSE;
      }

      $image_render_array = $media->get($media_source_field)->view([
        'label'    => 'hidden',
        'type'     => 'responsive_image',
        'settings' => [
          'responsive_image_style' => $image_style,
        ],
      ]);

      return $image_render_array ?? FALSE;
    }
  }

  /**
   * A styled thumbnail image.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   The taxonomy object.
   *
   * @return mixed
   *   A renderable image or FALSE.
   */
  public static function prepareAuthorImage(Term $term) {
    $image_field = 'field_utnews_author_image';
    if (!$term->hasField($image_field) || $term->get($image_field)->isEmpty()) {
      return FALSE;
    }
    $main_image = $term->get($image_field);
    $image_style = 'utexas_image_style_140w_140h';
    if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($main_image->getString())) {
      $image_render_array = $media->field_utexas_media_image->view([
        'type' => 'image',
        'label' => 'hidden',
        'settings' => [
          'image_style' => $image_style,
          'image_link' => '',
        ],
      ]);
      return $image_render_array;
    }
  }

  /**
   * Provide a link Title field.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return Drupal\Core\Link
   *   A link object to points to either the node or external URL.
   */
  public static function prepareLinkedTitle(Node $node) {
    $link_field = 'field_utnews_external_link';
    // Use external link instead of node uri if present.
    if ($node->hasField($link_field) && !$node->get($link_field)->isEmpty()) {
      $link_object = [
        'link' => $node->get($link_field)->getValue()[0],
      ];
      return UtexasLinkOptionsHelper::buildLink($link_object, [], $node->getTitle());
    }
    return $node->toLink();
  }

  /**
   * Inserts a new key/value after the key in the array.
   *
   * @todo Consider moving to the kernel when required someplace else.
   *
   * @param string $key
   *   The key to insert after.
   * @param array $array
   *   An array to insert in to.
   * @param string $new_key
   *   The key to insert.
   * @param string $new_value
   *   An value to insert.
   *
   * @return array
   *   The new array if the key exists, the old array otherwise.
   */
  public static function arrayInsertAfter($key, array &$array, $new_key, $new_value) {
    if (array_key_exists($key, $array)) {
      $new = [];
      foreach ($array as $k => $value) {
        $new[$k] = $value;
        if ($k === $key) {
          $new[$new_key] = $new_value;
        }
      }
      return $new;
    }
    return $array;
  }

  /**
   * Inserts a new key/value before the key in the array.
   *
   * @todo Consider moving to the kernel when required someplace else.
   *
   * @param string $key
   *   The key to insert before.
   * @param array $array
   *   An array to insert in to.
   * @param string $new_key
   *   The key to insert.
   * @param string $new_value
   *   An value to insert.
   *
   * @return array
   *   The new array if the key exists, the old array otherwise.
   *
   * @see array_insert_after()
   */
  public static function arrayInsertBefore($key, array &$array, $new_key, $new_value) {
    if (array_key_exists($key, $array)) {
      $new = [];
      foreach ($array as $k => $value) {
        if ($k === $key) {
          $new[$new_key] = $new_value;
        }
        $new[$k] = $value;
      }
      return $new;
    }
    return $array;
  }

  /**
   * Helper function to place AddToAny block on News content type.
   */
  public static function addSocialSharing() {
    // Set Social Sharing links to display on News articles.
    if ($block = Block::load('speedway_addtoany_utexas')) {
      $block->enable();
      $visibility = $block->getVisibility();
      if (isset($visibility['entity_bundle:node']['bundles'])) {
        $visibility['entity_bundle:node']['bundles']['utnews_news'] = 'utnews_news';
        $block->setVisibilityConfig("entity_bundle:node", [
          'bundles' => $visibility['entity_bundle:node']['bundles'],
          'negate' => $visibility['entity_bundle:node']['negate'],
          'context_mapping' => $visibility['entity_bundle:node']['context_mapping'],
        ]);
        $block->save();
        if ($legacy_block = Block::load('addtoany_utnews')) {
          $legacy_block->delete();
        }
      }
    }
  }

}
