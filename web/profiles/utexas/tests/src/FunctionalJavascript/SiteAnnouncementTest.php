<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test all aspects of site announcement functionality.
 */
#[RunTestsInSeparateProcesses]
class SiteAnnouncementTest extends FunctionalJavascriptTestBase {

  /**
   * Initial action for all Site Announcement tests.
   */
  public function testSiteAnnouncement() {
    // Copy over icons files.
    $remote_paths = $this->copySiteAnnouncementIconFiles();

    $this->verifyIcons($remote_paths);
    $this->verifyColorSchemes();
    $this->verifySiteAnnouncementCreation();
  }

  /**
   * Privileged users can create, edit, & delete site announcements.
   *
   * @param array $remote_paths
   *   Returns array of copied files (filename => realPath).
   */
  public function verifyIcons(array $remote_paths) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: READ.
    $this->drupalLogin($this->initializeSiteManager(
      ['administer utexas announcement icons']
    ));
    // Verify 3 default icons are present upon installation.
    $this->drupalGet('/admin/config/site-announcement/icons');
    $assert->pageTextContains('Beacon');
    $assert->pageTextContains('Bullhorn');
    $assert->pageTextContains('Warning');

    // CRUD: CREATE
    // Add a new icon.
    $this->drupalGet('/admin/config/site-announcement/icons/add');
    $form = $this->waitForForm('utexas-announcement-icon-add-form');
    $form->fillField('label', 'Test Icon');
    // Wait on the "auto-generated" machine name JS.
    $assert->waitForElement('xpath', '//span[@class="machine-name-value"][string-length(text()) > 0]');
    $form->attachFileToField('files[icon]', $remote_paths['beacon.svg']);
    $this->clickInputByValue($form, 'Save');

    // CRUD: READ.
    $assert->pageTextContains('Test Icon');

    // CRUD: UPDATE
    // Edit the icon.
    $this->drupalGet('/admin/config/site-announcement/icons/test_icon/edit');
    $form = $this->waitForForm('utexas-announcement-icon-edit-form');
    $form->fillField('label', 'Change Icon');
    $form->pressButton('Save');

    // CRUD: READ.
    $this->drupalGet('/admin/config/site-announcement/icons');
    $assert->pageTextContains('Change Icon');

    // CRUD: DELETE
    // Delete the icon.
    $this->drupalGet('/admin/config/site-announcement/icons/test_icon/delete');
    $form = $this->waitForForm('utexas-announcement-icon-delete-form');
    $form->pressButton('Delete');

    // CRUD: READ.
    $this->drupalGet('/admin/config/site-announcement/icons');
    $assert->pageTextNotContains('Change Icon');
  }

  /**
   * Test the color scheme functionality.
   */
  public function verifyColorSchemes() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: READ.
    $this->drupalLogin($this->initializeSiteManager(
      ['administer utexas announcement color schemes']
    ));
    // Verify 4 Default color schemes are present.
    $this->drupalGet('/admin/config/site-announcement/color-scheme');
    $assert->pageTextContains('Green Background, White Text');
    $assert->pageTextContains('Grey Background, White Text');
    $assert->pageTextContains('Orange Background, Black Text');
    $assert->pageTextContains('Yellow Background, Black Text');

    // CRUD: CREATE
    // Add a new color scheme.
    $this->drupalGet('/admin/config/site-announcement/color-scheme/add');
    $form = $this->waitForForm('utexas-announcement-color-scheme-add-form');
    $form->fillField('label', 'Test Color');
    // Wait on the "auto-generated" machine name JS.
    $assert->waitForElement('xpath', '//span[@class="machine-name-value"][string-length(text()) > 0]');
    $form->fillField('background_color', '#bf5700');
    $form->fillField('text_color', '#ffffff');
    $form->pressButton('Save');

    // CRUD: READ.
    $this->drupalGet('/admin/config/site-announcement/color-scheme');
    $assert->pageTextContains('Test Color');
    $assert->pageTextContains('#bf5700');
    $assert->pageTextContains('#ffffff');

    // CRUD: UPDATE
    // Edit the color scheme.
    $this->drupalGet('/admin/config/site-announcement/color-scheme/test_color/edit');
    $form = $this->waitForForm('utexas-announcement-color-scheme-edit-form');
    $form->fillField('label', 'Change Color Scheme');
    $form->fillField('background_color', '#a6cd57');
    $form->fillField('text_color', '#f8971f');
    $form->pressButton('Save');

    // CRUD: READ.
    $this->drupalGet('/admin/config/site-announcement/color-scheme');
    $assert->pageTextContains('Change Color Scheme');
    $assert->pageTextContains('#a6cd57');
    $assert->pageTextContains('#f8971f');

    // CRUD: DELETE
    // Delete the color scheme.
    $this->drupalGet('/admin/config/site-announcement/color-scheme/test_color/delete');
    $form = $this->waitForForm('utexas-announcement-color-scheme-delete-form');
    $form->pressButton('Delete');

    // CRUD: READ.
    $this->drupalGet('/admin/config/site-announcement/color-scheme');
    $assert->pageTextNotContains('Change Color Scheme');
  }

  /**
   * Test the announcement creation functionality.
   */
  public function verifySiteAnnouncementCreation() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Create a test node.
    $basic_page_id = $this->createBasicPage();

    // CRUD: CREATE.
    $this->drupalLogin($this->initializeSiteManager(
      ['manage utexas site announcement']
    ));
    $this->drupalGet('/admin/config/site-announcement');
    $form = $this->waitForForm('utexas-site-announcement-config');
    $form->fillField('state', 'all');
    $form->fillField('title', 'Tornado warning');
    $form->fillField('icon', 'beacon');
    $form->fillField('scheme', 'yellow_black');
    $form->fillField('message[value]', 'It looks like we got a tornado coming, folks! Take shelter!');
    $form->fillField('cta[uri]', 'https://tylerfahey.com');
    $form->fillField('cta[title]', 'How to get help');
    $form->fillField('cta[options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('cta[options][attributes][class]', 'ut-cta-link--external');
    $form->pressButton('Save configuration');

    // CRUD: READ.
    // Verify the announcement is visible on homepage & the test node.
    $pages_to_test = [
      '/node/' . $basic_page_id,
      '<front>',
    ];
    foreach ($pages_to_test as $path) {
      $this->drupalGet($path);
      $assert->elementTextContains('css', 'h2.announcement-title', "Tornado warning");
      $assert->elementTextContains('css', '.announcement-body', 'It looks like we got a tornado coming, folks! Take shelter!');
      $assert->elementTextContains('css', '#block-siteannouncement .cta a.ut-cta-link--external.ut-btn', 'How to get help');
      $assert->elementAttributeContains('css', '#block-siteannouncement .cta a.ut-cta-link--external.ut-btn', 'href', 'https://tylerfahey.com');
      $assert->elementAttributeContains('css', '#block-siteannouncement .cta a.ut-cta-link--external.ut-btn', 'target', '_blank');
      $assert->elementAttributeContains('css', '#block-siteannouncement .cta a.ut-cta-link--external.ut-btn', 'rel', 'noopener noreferrer');
      $assert->responseContains('#ffeb63');
      $assert->responseContains('#000000');
      // Beginning of beacon.svg data.
      $assert->responseContains('M16 8c0-4.418-3.582-8-8-8s-8 3.582-8 8c0 3.438 2.169 6.37');
    }

    // CRUD: UPDATE
    // Go back and change to "homepage only" and change some other config.
    $this->drupalGet('/admin/config/site-announcement');
    $form = $this->waitForForm('utexas-site-announcement-config');
    $form->fillField('state', 'homepage');
    $form->fillField('icon', 'bullhorn');
    $form->fillField('scheme', 'green_white');
    $form->fillField('cta[uri]', 'https://utexas.edu');
    $form->pressButton('Save configuration');

    // CRUD: READ
    // Verify the announcement is visible on the homepage.
    $this->drupalGet('<front>');
    $assert->elementTextContains('css', 'h2.announcement-title', "Tornado warning");
    $assert->elementTextContains('css', '.announcement-body', 'It looks like we got a tornado coming, folks! Take shelter!');
    $assert->elementTextContains('css', '#block-siteannouncement .cta a.ut-btn', 'How to get help');
    $assert->elementAttributeContains('css', '#block-siteannouncement .cta a.ut-btn', 'href', 'https://utexas.edu');
    $assert->responseContains('#43695b');
    $assert->responseContains('#ffffff');
    // Beginning of bullhorn.svg data.
    $assert->responseContains('M16 6.707c0-3.139-0.919-5.687-2.054-5.707 0.005-0');

    // CRUD: READ
    // Verify the announcement is not visible on test node.
    $this->drupalGet('/node/' . $basic_page_id);
    $assert->elementNotExists('css', '#site-announcement');
    $assert->responseNotContains('#43695b');
    $assert->responseNotContains('#ffffff');

    // CRUD: UPDATE
    // Go back and change announcement to "Inactive" display setting.
    $this->drupalGet('/admin/config/site-announcement');
    $form = $this->waitForForm('utexas-site-announcement-config');
    $form->fillField('state', 'inactive');
    $form->pressButton('Save configuration');

    // CRUD: READ
    // Verify the announcement is not visible on homepage & the test node.
    $pages_to_test = [
      '/node/' . $basic_page_id,
      '<front>',
    ];
    foreach ($pages_to_test as $path) {
      $this->drupalGet($path);
      $assert->elementNotExists('css', '#site-announcement');
      $assert->responseNotContains('#43695b');
      $assert->responseNotContains('#ffffff');
    }

    // CRUD: DELETE.
    $this->removeNodes([$basic_page_id]);
  }

}
