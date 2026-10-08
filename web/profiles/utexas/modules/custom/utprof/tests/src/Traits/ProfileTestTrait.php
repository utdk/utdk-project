<?php

namespace Drupal\Tests\utprof\Traits;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * General-purpose methods for interacting with Profile nodes.
 */
trait ProfileTestTrait {

  /**
   * Populates & saves a Profile node to the database.
   *
   * @param string $media_id
   *   The media entity ID.
   *
   * @return int
   *   The new node's internal ID.
   */
  protected function createProfileNode($media_id) {
    $node = Node::create([
      'type'                            => 'utprof_profile',
      'title'                           => 'Test Profile Page title',
      'moderation_state'                => ['target_id' => 'published'],
      'field_utprof_add_basic_info'     => [
        'value' => 'Basic info text',
        'format' => 'plain_text',
      ],
      'field_utprof_add_contact_info'   => [
        'value' => '<p>Contact info text</p>',
        'format' => 'plain_text',
      ],
      'field_utprof_building_code'      => 'FAC',
      'field_utprof_building_room_numb' => 'Room Number',
      'field_utprof_contact_form_link'  => [
        'uri' => 'https://www.google.com',
        'title' => 'Contact me here',
      ],
      'field_utprof_content'     => [
        0 => [
          'header' => 'Test Profile Page content tab 1 header text',
          'body_value' => '<p>Test Profile Page content tab 1 body text</p>',
          'body_format' => 'plain_text',
        ],
        1 => [
          'header' => 'Test Profile Page content tab 2 header text',
          'body_value' => '<p>Test Profile Page content tab 2 body text</p>',
          'body_format' => 'plain_text',
        ],
      ],
      'field_utprof_image_ratio_opt'    => 1,
      'field_utprof_designation'        => [
        0 => ['value' => 'Designation 1'],
        1 => ['value' => 'Designation 2'],
      ],
      'field_utprof_eid'                => 'myeid',
      'field_utprof_email_address'      => 'email@address.com',
      'field_utprof_fax_number'         => '512-123-4567',
      'field_utprof_given_name'         => 'Jane',
      'field_utprof_basic_media'        => ['target_id' => $media_id],
      'field_utprof_listing_link'       => 1,
      'field_utprof_phone_number'       => '512-234-5678',
      'field_utprof_profile_groups'     => [
        0 => ['target_id' => '1'],
        1 => ['target_id' => '2'],
      ],
      'field_utprof_profile_tags'       => [
        0 => ['target_id' => '1'],
        1 => ['target_id' => '2'],
      ],
      'field_utprof_surname'            => 'Doe',
      'field_utprof_website_link'       => [
        'uri' => 'https://www.yahoo.com',
      ],
    ]);
    $node->save();
    return $node;
  }

  /**
   * Populates saves a sample terms for the vocabulary to the database.
   *
   * @return object[]|\Drupal\taxonomy\TermInterface[]
   *   An array of term objects that are the children of the vocabulary $vid.
   */
  protected function createProfileTags() {
    $profile_tags = ['Internal Tag 1', 'Internal Tag 2', 'Internal Tag 3'];
    $vid = 'utprof_tags';
    $weight = 0;
    foreach ($profile_tags as $term_name) {
      $term = Term::create([
        'name' => $term_name,
        'vid' => $vid,
        'weight' => $weight,
      ]);
      $term->save();
      $weight++;
    }

    /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    return $term_storage->loadTree($vid);
  }

  /**
   * Populates saves a sample terms for the vocabulary to the database.
   *
   * @return object[]|\Drupal\taxonomy\TermInterface[]
   *   An array of term objects that are the children of the vocabulary $vid.
   */
  protected function createProfileGroups() {
    $profile_groups = ['Group 1', 'Group 2', 'Group 3'];
    $vid = 'utprof_groups';
    $weight = 0;
    foreach ($profile_groups as $term_name) {
      $term = Term::create([
        'name' => $term_name,
        'vid' => $vid,
        'weight' => $weight,
      ]);
      $term->save();
      $weight++;
    }

    /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    return $term_storage->loadTree($vid);
  }

}
