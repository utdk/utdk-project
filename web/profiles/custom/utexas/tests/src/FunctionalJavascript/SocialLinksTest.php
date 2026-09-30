<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test all aspects of Social Links functionality.
 */
#[RunTestsInSeparateProcesses]
class SocialLinksTest extends FunctionalJavascriptTestBase {

  /**
   * Initial action for all tests.
   */
  public function testSocialLinks() {
    // Copy over tests files.
    $test_files_remote_paths = $this->copyTestFiles();

    // Copy over module icons files.
    $this->copySocialLinksIconFiles();

    $this->drupalLogin($this->initializeSiteManager(
      ['administer utexas announcement icons']
    ));

    $this->verifyBlockInterface();
    $this->verifySocialLinks($test_files_remote_paths);
    $this->verifyBlock();
  }

  /**
   * Verify functionality of the custom Social Links block interface.
   */
  public function verifyBlockInterface() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: READ.
    $this->drupalGet('block/add/social_links');
    $form = $this->waitForForm('block-content-social-links-form');
    // Verify that all default icons are present on block admin page.
    $social_link_options = $form->findAll('css', '#edit-field-utexas-sl-social-links-0-social-account-links-0-social-account-name option');
    $available_icons = [
      'facebook',
      'flickr',
      'instagram',
      'linkedin',
      'pinterest',
      'reddit',
      'snapchat',
      'tumblr',
      'x',
      'vimeo',
      'youtube',
    ];
    foreach (array_values($social_link_options) as $option) {
      $this->assertTrue(in_array($option->getValue(), $available_icons), $option->getValue() . 'is available');
    }
    // Verify the correct field schemae exist.
    $fields = [
      'edit-info-0-value',
      'edit-field-utexas-sl-social-links-0-headline',
      'edit-field-utexas-sl-social-links-0-icon-size-ut-social-links-small',
      'edit-field-utexas-sl-social-links-0-icon-size-ut-social-links-medium',
      'edit-field-utexas-sl-social-links-0-icon-size-ut-social-links-large',
      'edit-field-utexas-sl-social-links-0-social-account-links-0-social-account-name',
      'edit-field-utexas-sl-social-links-0-social-account-links-0-social-account-url',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Verify functionality of the Social Link entity.
   *
   * @param array $test_files_remote_paths
   *   Returns array of copied files (filename => realPath).
   */
  public function verifySocialLinks(array $test_files_remote_paths) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: READ
    // Verify default icons are present upon installation.
    $this->drupalGet("/admin/structure/social-links");
    $available_icons = [
      'facebook',
      'flickr',
      'instagram',
      'linkedin',
      'pinterest',
      'reddit',
      'snapchat',
      'tumblr',
      'x',
      'vimeo',
      'youtube',
    ];
    foreach ($available_icons as $icon) {
      $assert->pageTextContains($icon);
    }

    // CRUD: CREATE
    // Add a Social Link.
    $this->drupalGet('/admin/structure/social-links/add');
    $form = $this->waitForForm('utexas-social-links-data-add-form');
    $form->fillField('label', 'Test Link');
    // Wait on the "auto-generated" machine name JS.
    $assert->waitForElement('xpath', '//span[@class="machine-name-value"][string-length(text()) > 0]');
    $form->attachFileToField('files[icon]', $test_files_remote_paths['check.svg']);
    $this->clickInputByValue($form, 'Save');

    // CRUD: READ.
    $assert->pageTextContains('Test Link');
    $assert->responseContains('Find us on Facebook');

    // CRUD: UPDATE
    // Edit the link.
    $this->drupalGet('/admin/structure/social-links/test_link/edit');
    $form = $this->waitForForm('utexas-social-links-data-edit-form');
    $form->fillField('label', 'Change Link');
    $this->clickInputByValue($form, 'Save');

    // CRUD: READ.
    $this->drupalGet('/admin/structure/social-links');
    $assert->pageTextContains('Change Link');

    // CRUD: UPDATE
    // Go back and change icon.
    $this->drupalGet('/admin/structure/social-links/test_link/edit');
    $form = $this->waitForForm('utexas-social-links-data-edit-form');
    $form->attachFileToField('files[icon]', $test_files_remote_paths['location.svg']);
    $this->clickInputByValue($form, 'Save');

    // CRUD: READ
    // Confirm social link is rendering with new svg path.
    $this->drupalGet('/admin/structure/social-links');
    $assert->responseContains('<path d="M5.4749999,0 C2.43935876,0 0,2.45021982 0,5.50153207 C0,8.5518841 5.4749999,16.0038459 5.4749999,16.0038459 C5.4749999,16.0038459 10.9499998,8.5518841 10.9499998,5.50153207 C10.9499998,2.45021982 8.51064105,0 5.4749999,0 Z M5.89615374,8.00192294 C4.48158136,8.00192294 3.36923071,6.89054251 3.36923071,5.4749999 C3.36923071,4.06042752 4.48061114,2.94807687 5.89615374,2.94807687 C7.31072613,2.94807687 8.42307678,4.0594573 8.42307678,5.4749999 C8.42307678,6.89051825 7.31075039,8.00192294 5.89615374,8.00192294 Z"></path>');
  }

  /**
   * Verify functionality of the custom Social Links block.
   */
  public function verifyBlock() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Social Links';
    $block_type_id = 'social_links';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Social Links Item 1 fields.
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][0][social_account_name]', 'instagram');
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][0][social_account_url]', 'https://instagram.com/our-site');
    // Add Social Links Item 2 and fill fields.
    $this->addNonDraggableFormItem($form, 'Add social link item');
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][1][social_account_name]', 'x');
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][1][social_account_url]', 'https://twitter.com/our-site');
    // Add Social Links Item 3 and fill fields.
    $this->addNonDraggableFormItem($form, 'Add social link item');
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][2][social_account_name]', 'facebook');
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][2][social_account_url]', 'https://facebook.com/our-site');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: UPDATE.
    // Remove "middle" link from list.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Fill Social Link Item 2 fields.
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][1][social_account_url]', '');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Verify subsequent links persist after previous links are removed.
    $assert->fieldValueEquals('field_utexas_sl_social_links[0][social_account_links][1][social_account_name]', 'facebook');
    $assert->fieldValueEquals('field_utexas_sl_social_links[0][social_account_links][1][social_account_url]', 'https://facebook.com/our-site');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, 'Sitewide Social Media Links');
    $form = $this->waitForForm($block_content_edit_form_id);
    // Edit the default social links block and add test network and headline.
    $form->fillField('field_utexas_sl_social_links[0][headline]', 'Headline Test');
    $form->fillField('field_utexas_sl_social_links[0][icon_size]', 'ut-social-links--large');
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][0][social_account_name]', 'test_link');
    $form->fillField('field_utexas_sl_social_links[0][social_account_links][0][social_account_url]', 'https://testsocial.com');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' Sitewide Social Media Links has been updated.');

    // CRUD: READ
    // Go to homepage and confirm test network is rendering with test svg path.
    $this->drupalGet("<front>");
    $assert->responseContains('https://testsocial.com');
    $assert->responseContains('Headline Test');
    // User-selected icon size is reflected in markup.
    $assert->elementExists('css', '.block__ut-social-links.ut-social-links--large');
    $assert->pageTextContains('Find us on Test_link');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Save block.
    $form->pressButton('Save');

    // CRUD: READ
    // Verify that an external link is required for the URL.
    $assert->statusMessageContainsAfterWait('1 error has been found');

    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

}
