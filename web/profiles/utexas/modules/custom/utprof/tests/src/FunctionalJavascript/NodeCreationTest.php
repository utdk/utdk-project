<?php

namespace Drupal\Tests\utprof\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test all aspects of Profile Node creation functionality.
 */
#[RunTestsInSeparateProcesses]
class NodeCreationTest extends TestBase {

  /**
   * Test Profile content type and output.
   */
  public function testCreateProfileNode() {
    // Enlarge the viewport so that all is clickable.
    $this->getSession()->resizeWindow(1200, 3000);
    $this->drupalGet('node/add/utprof_profile');

    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    // Add field values for second tab.
    // Click on second horizontal tab ("Basic Information").
    $horizontal_tabs = $page->findAll('css', '.horizontal-tab-button-1');
    $horizontal_tabs[0]->click();
    // Add field values.
    $page->fillField('title[0][value]', 'Test Profile Page title');
    $page->fillField('field_utprof_given_name[0][value]', 'Profile Test Given Name');
    $page->fillField('field_utprof_surname[0][value]', 'Profile Test Surname');
    $page->fillField('field_utprof_eid[0][value]', 'myeid');
    $page->fillField('field_utprof_designation[0][value]', 'Designation 1');
    // Add additional designation field value.
    $page->pressButton('edit-field-utprof-designation-add-more');
    $assert->assertWaitOnAjaxRequest();
    $page->fillField('field_utprof_designation[1][value]', 'Designation 2');
    // Add media item.
    $page->pressButton('edit-field-utprof-basic-media-open-button');
    $assert->assertWaitOnAjaxRequest();
    // Select the test media item ("Image 1" with file name "test-image.png").
    $assert->elementExists('css', 'img[src*="' . $this->testMediaImageFilename . '"]')->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $assert->assertWaitOnAjaxRequest();
    // Assign value using CKEditor enabled field.
    $this->fillCkeditorField('.form-item--field-utprof-add-basic-info-0-value', 'Basic info text');

    // Click on fourth horizontal tab ("Contact Information").
    $horizontal_tabs = $page->findAll('css', '.horizontal-tab-button-3');
    $horizontal_tabs[0]->click();
    // Add field values.
    $page->fillField('field_utprof_email_address[0][value]', 'email@address.com');
    $page->fillField('field_utprof_website_link[0][uri]', 'https://www.yahoo.com');
    $page->fillField('field_utprof_website_link[0][title]', 'Profile Test Website Link title');
    $page->fillField('field_utprof_building_code[0][value]', 'FAC');
    $page->fillField('field_utprof_building_room_numb[0][value]', 'Room Number');
    $page->fillField('field_utprof_fax_number[0][value]', '512-123-4567');
    $page->fillField('field_utprof_phone_number[0][value]', '512-234-5678');
    $page->fillField('field_utprof_contact_form_link[0][uri]', 'https://www.google.com');
    $page->fillField('field_utprof_contact_form_link[0][title]', 'Contact me here');
    // Assign value using CKEditor enabled field.
    $this->fillCkeditorField('.form-item--field-utprof-add-contact-info-0-value', 'Contact info text');

    // Click on third horizontal tab ("Main Content").
    $horizontal_tabs = $page->findAll('css', '.horizontal-tab-button-2');
    $horizontal_tabs[0]->click();
    $page->fillField('field_utprof_content[0][header]', 'Test Profile Page content tab 1 header text');
    // Assign value using CKEditor enabled field.
    $this->fillCkeditorField('.form-item--field-utprof-content-0-body-value', 'Test Profile Page content tab 1 body text');
    // Add additional content tab values.
    $page->pressButton('edit-field-utprof-content-add-more');
    $assert->assertWaitOnAjaxRequest();
    $page->fillField('field_utprof_content[1][header]', 'Test Profile Page content tab 2 header text');
    // Assign value using CKEditor enabled field.
    $assert->waitForElement('css', '.form-item--field-utprof-content-1-body-value .ck-editor__editable');
    $this->fillCkeditorField('.form-item--field-utprof-content-1-body-value', 'Test Profile Page content tab 2 body text');

    // Submit form.
    $page->pressButton('edit-submit');
    // Content creation confirmation message includes a link, so we only verify
    // the end of the message.
    $assert->waitForText('has been created.');

    $node = $this->getNodeByTitle('Test Profile Page title');
    $this->verifyProfileViewModeBasic($node);
    $this->verifyProfileViewModeDefault($node);
    $this->verifyProfileViewModeFull($node);
    $this->verifyProfileViewModeNameOnly($node);
    $this->verifyProfileViewModeProminent($node);
    $this->verifyProfileViewModeTeaser($node);
  }

}
