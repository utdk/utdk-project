<?php

namespace Drupal\Tests\utprof\Traits\ViewModeTests;

/**
 * Method for verfiying display of node view mode.
 */
trait ProfileViewModeProminentTrait {

  /**
   * Test Profile node "Prominent" view mode.
   */
  public function verifyProfileViewModeProminent($node) {
    $build = $this->entityTypeManager->getViewBuilder('node')->view($node, 'utexas_prominent');
    $output = (string) $this->renderer->renderPlain($build);

    $fields_contains = [
      'title' => 'Test Profile Page title',
      'field_utprof_add_basic_info' => 'Basic info text',
      'field_utprof_designation-0' => 'Designation 1',
      'field_utprof_designation-1' => 'Designation 2',
      'field_utprof_eid' => 'https://directory.utexas.edu/index.php?q=myeid',
      'field_utprof_basic_media' => 'image-test.png',
      'field_utprof_email_address' => 'email@address.com',
    ];
    $fields_not_contains = [
      'field_utprof_add_contact_info' => 'Contact info text',
      'field_utprof_building_code' => 'FAC',
      'field_utprof_building_room_numb' => 'Room Number',
      'field_utprof_contact_form_link' => 'Contact me here',
      'field_utprof_content-0-header' => 'Test Profile Page content tab 1 header text',
      'field_utprof_content-0-body' => 'Test Profile Page content tab 1 body text',
      'field_utprof_content-1-header' => 'Test Profile Page content tab 2 header text',
      'field_utprof_content-1-body' => 'Test Profile Page content tab 2 body text',
      'field_utprof_fax_number' => '512-123-4567',
      'field_utprof_phone_number' => '512-234-5678',
      'field_utprof_profile_tags-0' => 'Internal Tag 1',
      'field_utprof_profile_tags-1' => 'Internal Tag 2',
      'field_utprof_profile_tags-2' => 'Internal Tag 3',
      'field_utprof_website_link' => 'https://www.yahoo.com',
    ];

    foreach ($fields_contains as $value) {
      $this->assertStringContainsString($value, $output);
    }

    foreach ($fields_not_contains as $value) {
      $this->assertStringNotContainsString($value, $output);
    }
  }

}
