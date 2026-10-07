<?php

namespace Drupal\utprof_content_type_profile;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;
use Drupal\utprof_content_type_profile\Form\FormAlter;

/**
 * Business logic for rendering the content type.
 */
class ProfileContentTypeHelper {

  /**
   * Helper function to determine whether to link.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   Whether or not to link to the node.
   */
  public static function doLinkToNode(Node $node) {
    if ($node->hasField('field_utprof_listing_link') && !$node->get('field_utprof_listing_link')->isEmpty()) {
      $do_link_title = $node->get('field_utprof_listing_link')->getString();
      if ($do_link_title) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * A themed media item.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   An image render array
   */
  public static function getBasicMedia(Node $node) {
    // Check for media field.
    $media_field = 'field_utprof_basic_media';
    if (!$node->hasField($media_field) || $node->$media_field->isEmpty()) {
      return FALSE;
    }

    // Get media item id.
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $field_items_list */
    $field_items_list = $node->get($media_field);
    $media_id = $field_items_list->getString();

    // Determine image style.
    if ($node->hasField('field_utprof_image_ratio_opt') && !$node->get('field_utprof_image_ratio_opt')->isEmpty()) {
      $use_primary_image_style = $node->get('field_utprof_image_ratio_opt');
    }
    $user_defined_image_style = $use_primary_image_style ? 'utexas_image_style_500w_500h' : 'utexas_image_style_500w';

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
        'type' => 'image',
        'label' => 'hidden',
        'settings' => [
          'image_style' => $user_defined_image_style,
          'image_link' => '',
        ],
      ]);
      \Drupal::service('renderer')->addCacheableDependency($image_render_array, $media);
    }

    return $image_render_array ?? FALSE;
  }

  /**
   * Return an array of building code link & room number.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   The building code information array.
   */
  public static function getBuildingInformation(Node $node) {
    // Build Building Information link.
    $building_information = [];
    if ($node->hasField('field_utprof_building_code') && !$node->get('field_utprof_building_code')->isEmpty()) {
      $building_code = $node->get('field_utprof_building_code')->getString();
      $url = Url::fromUri('https://utdirect.utexas.edu/apps/campus/buildings/nlogon/maps/UTM/' . $building_code);
      $link_options = [
        'attributes' => [
          'class' => [
            'ut-cta-link--external',
          ],
        ],
      ];
      $url->setOptions($link_options);
      $building_code_link = Link::fromTextAndUrl($building_code, $url);
    }
    if ($node->hasField('field_utprof_building_room_numb') && !$node->get('field_utprof_building_room_numb')->isEmpty()) {
      $building_room_value = $node->get('field_utprof_building_room_numb')->getString();
    }
    $building_information['building_code'] = $building_code_link ?? NULL;
    $building_information['room_number'] = $building_room_value ?? NULL;
    return $building_information;
  }

  /**
   * Link to user-provided phone.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   A renderable Drupal link.
   */
  public static function getPhone(Node $node) {
    // Build phone number link.
    $phone = [];
    if ($node->hasField('field_utprof_phone_number') && !$node->get('field_utprof_phone_number')->isEmpty()) {
      $phone_number = $node->get('field_utprof_phone_number');
      $phone_number_text = $phone_number->getString();
      $phone_link = Url::fromUri('tel:' . rawurlencode($phone_number_text));
      $phone = Link::fromTextAndUrl($phone_number_text, $phone_link);
    }
    return $phone;
  }

  /**
   * Link to user-provided email.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   A renderable Drupal link.
   */
  public static function getEmail(Node $node) {
    // Build email link.
    $email = [];
    if ($node->hasField('field_utprof_display_email') && $node->get('field_utprof_display_email')->getValue()[0]['value'] !== '0') {
      if ($node->hasField('field_utprof_email_address') && !$node->get('field_utprof_email_address')->isEmpty()) {
        $email = $node->get('field_utprof_email_address');
        $email_text = $email->getString();
        $email_link = Url::fromUri('mailto:' . rawurlencode($email_text));
        $email = Link::fromTextAndUrl($email_text, $email_link);
      }
    }
    return $email;
  }

  /**
   * The node title, linked depending on user selection.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   * @param string $link_class
   *   Optional view mode, setting which link class to use.
   *
   * @return array
   *   A renderable Drupal link.
   */
  public static function getPreparedTitle(Node $node, $link_class = '') {
    // Build a title field, linked conditionally.
    $title = $node->getTitle();
    if (self::doLinkToNode($node)) {
      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
      if (isset($link_class)) {
        $link_options = [
          'attributes' => [
            'class' => [
              $link_class,
            ],
          ],
        ];
        $url->setOptions($link_options);
      }
      return Link::fromTextAndUrl($title, $url);
    }
    return $title;
  }

  /**
   * Link to user-provided site or form.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   A renderable Drupal link.
   */
  public static function getContactLink(Node $node) {
    // Build contact form link.
    $link = [];
    if ($node->hasField('field_utprof_contact_form_link') && !$node->get('field_utprof_contact_form_link')->isEmpty()) {
      $contact_link = $node->get('field_utprof_contact_form_link');
      $contact_values = $contact_link->getValue();
      $contact_link = [
        'link' => $contact_values[0],
      ];
      $link = UtexasLinkOptionsHelper::buildLink($contact_link, ['ut-btn']);
    }
    return $link;
  }

  /**
   * Evaluate Contact field values needed for the display of contact info block.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return bool
   *   Whether or not there are contact info fields.
   */
  public static function hasContactInfoFields(Node $node) {
    $has_contact_info = FALSE;
    foreach (FormAlter::UTPROF_CONTENT_TYPE_PROFILE_CONTACT_INFO_FIELDS as $contact_info_field) {
      if ($node->hasField($contact_info_field) && !$node->$contact_info_field->isEmpty()) {
        $has_contact_info = TRUE;
        break;
      }
    }

    // The email address field is not grouped with the other contact info fields
    // when rendering the node form. However, it is grouped with the other
    // contact info fields when rendering the node. Therefore, we check for it
    // separately here. See FormAlter::alterProfileNodeForm() for form usage of
    // FormAlter::UTPROF_CONTENT_TYPE_PROFILE_CONTACT_INFO_FIELDS.
    if ($node->hasField('field_utprof_email_address') && !$node->get('field_utprof_email_address')->isEmpty()) {
      $has_contact_info = TRUE;
    }

    return $has_contact_info;
  }

  /**
   * Link to UTexas Directory with EID parameter.
   *
   * @param Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return array
   *   A renderable Drupal link.
   */
  public static function getDirectoryLink(Node $node) {
    // Build EID link.
    $directory_link = [];
    if ($node->hasField('field_utprof_eid') && !$node->get('field_utprof_eid')->isEmpty()) {
      $eid = $node->get('field_utprof_eid');
      $url = Url::fromUri('https://directory.utexas.edu/index.php?q=' . $eid->getString());
      $link_options = [
        'attributes' => [
          'class' => [
            'ut-link--darker',
            'ut-cta-link--lock',
          ],
        ],
      ];
      $url->setOptions($link_options);
      $directory_link = Link::fromTextAndUrl(t('View in UT Directory'), $url);
    }
    return $directory_link;
  }

}
