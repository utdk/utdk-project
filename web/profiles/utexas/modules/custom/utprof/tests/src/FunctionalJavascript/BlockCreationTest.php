<?php

namespace Drupal\Tests\utprof\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test all aspects of Profile Listing block functionality.
 */
#[RunTestsInSeparateProcesses]
class BlockCreationTest extends TestBase {

  /**
   * Test Profile List block and output.
   */
  public function testCreateProfileListingBlock() {
    $session = $this->getSession();
    // Enlarge the viewport so that all is clickable.
    $session->resizeWindow(1200, 4000);
    $this->drupalGet('block/add/utprof_profile_listing');

    $page = $session->getPage();
    $assert = $this->assertSession();

    // Add field values.
    $page->fillField('info[0][value]', 'Test Profile Listing Block Description');
    // Set "Profiles Display Format" (node view mode) radio button.
    $page->selectFieldOption('edit-field-utprof-view-mode-nodeutexas-basic', 'node.utexas_basic');
    // Assign value using CKEditor enabled field.
    $this->fillCkeditorField('.form-item--field-utprof-header-0-value', 'Header text');
    // Set "Limit Profiles to the following group(s)" (taxonomy terms).
    $page->findField('field_utprof_profile_groups[1]')->click();
    // Assign value using CKEditor enabled field.
    $this->fillCkeditorField('.form-item--field-utprof-footer-0-value', 'Footer text');
    // Save block content.
    $page->pressButton('Save and configure');
    $assert->waitForText('Profile Listing Test Profile Listing Block Description has been created.');
    // Place block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save');
    $assert->waitForText('The block configuration has been saved.');
    // Verify page output.
    $this->drupalGet('<front>');
    $assert->elementExists('css', '.utexas-basic');

    // Load block by title to get the id.
    $block = $this->drupalGetBlockByInfo('Test Profile Listing Block Description');

    // Set display to "Name Only".
    $this->drupalGet('/admin/content/block/' . $block->id());
    // Set "Profiles Display Format" (node view mode) radio button.
    $page->selectFieldOption('edit-field-utprof-view-mode-nodeutexas-name-only', 'node.utexas_name_only');
    $page->pressButton('Save');
    $assert->waitForText('The block configuration has been saved.');
    // Verify page output.
    $this->drupalGet('<front>');
    $assert->elementExists('css', '.utexas-name-only');

    // Set display to "Prominent".
    $this->drupalGet('admin/content/block/' . $block->id());
    // Set "Profiles Display Format" (node view mode) radio button.
    $page->selectFieldOption('edit-field-utprof-view-mode-nodeutexas-prominent', 'node.utexas_prominent');
    $page->pressButton('Save');
    $assert->waitForText('The block configuration has been saved.');
    // Verify page output.
    $this->drupalGet('<front>');
    $assert->elementExists('css', '.utexas-prominent');
  }

}
