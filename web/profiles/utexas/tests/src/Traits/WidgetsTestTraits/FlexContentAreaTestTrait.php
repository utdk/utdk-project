<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies Flex Content Area A & B field schema & validation.
 */
trait FlexContentAreaTestTrait {

  /**
   * Comprehensively verify Flex Content Area behavior.
   */
  public function verifyFlexContentArea() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $session = $this->getSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $session->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Flex Content Area';
    $block_type_id = 'utexas_flex_content_area';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Flex Content Area 1 fields.
    $this->clickDetailsBySummaryText('New item');
    // Open the media library.
    $session->wait(3000);
    $page->pressButton('Add media');
    $this->assertTrue($assert->waitForText('Add or select media'));
    $assert->pageTextContains('image-test.png');
    // Select the first media item (should be "500x500.png").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));
    $form->fillField('field_block_fca[0][flex_content_area][headline]', 'Flex Content Area Headline 1');
    $form->fillField('field_block_fca[0][flex_content_area][copy][value]', 'Flex Content Area Copy');
    $this->clickDetailsBySummaryText('New Link');
    $form->fillField('field_block_fca[0][flex_content_area][cta_wrapper][link][uri]', 'https://utexas.edu');
    $form->fillField('field_block_fca[0][flex_content_area][cta_wrapper][link][title]', 'Flex Content Area Call to Action');
    $form->fillField('field_block_fca[0][flex_content_area][cta_wrapper][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_fca[0][flex_content_area][cta_wrapper][link][options][attributes][class]', 'ut-cta-link--external');
    // Fill Flex Content Area 1 Link 1 fields.
    $form->fillField('field_block_fca[0][flex_content_area][links][0][title]', 'Flex Content Area External Link');
    $form->fillField('field_block_fca[0][flex_content_area][links][0][uri]', 'https://utexas.edu');
    $form->fillField('field_block_fca[0][flex_content_area][links][0][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_fca[0][flex_content_area][links][0][options][attributes][class]', 'ut-cta-link--lock');
    // Add Flex Content Area 1 Link 2 and fill fields.
    $page->pressButton('Add link');
    $this->clickDetailsBySummaryText('New Link', 2);
    $form->fillField('field_block_fca[0][flex_content_area][links][1][title]', 'Flex Content Area Second Link');
    $form->fillField('field_block_fca[0][flex_content_area][links][1][uri]', 'https://second.test');
    // Add Flex Content Area 1 Link 3 and fill fields.
    $page->pressButton('Add link');
    $this->clickDetailsBySummaryText('New Link', 3);
    $form->fillField('field_block_fca[0][flex_content_area][links][2][title]', 'Flex Content Area Third Link');
    $form->fillField('field_block_fca[0][flex_content_area][links][2][uri]', 'https://third.test');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/' . $this->testImageId . '/edit/usage');
    $assert->pageTextContains('Content block: Flex Content Area');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: UPDATE
    // Add two more Flex Content Area instances.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Fill Flex Content Area 2 fields.
    $this->clickDetailsBySummaryText('New item');
    $form->fillField('field_block_fca[1][flex_content_area][headline]', 'Flex Content Area Headline 2');
    $form->fillField('field_block_fca[1][flex_content_area][copy][value]', 'Flex Content Area Copy 2');
    $form->fillField('field_block_fca[1][flex_content_area][cta_wrapper][link][uri]', 'https://utexas.edu');
    $form->fillField('field_block_fca[1][flex_content_area][cta_wrapper][link][title]', 'Flex Content Area Call to Action 2');
    // Fill Flex Content Area 2 Link 1 fields.
    $this->clickDetailsBySummaryText('New Link');
    $form->fillField('field_block_fca[1][flex_content_area][links][0][title]', 'Flex Content Area External Link 2');
    $form->fillField('field_block_fca[1][flex_content_area][links][0][uri]', 'https://utexas.edu');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');
    // Re-open block.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Fill Flex Content Area 3 fields.
    $this->clickDetailsBySummaryText('New item');
    $form->fillField('field_block_fca[2][flex_content_area][headline]', 'Flex Content Area Headline 3');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('node/' . $flex_page_id);
    // Flex Content Area instance 1 is rendered.
    $assert->elementTextContains('css', 'h3.ut-headline', 'Flex Content Area Headline');
    $assert->pageTextContains('Flex Content Area Copy');
    $assert->linkByHrefExists('https://utexas.edu');
    $assert->linkByHrefExists('https://second.test');
    $assert->linkByHrefExists('https://third.test');
    $assert->elementTextContains('css', 'a.ut-btn', 'Flex Content Area Call to Action');
    // Verify link exists with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-flex-content-area .ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify CTA not tabbable when headline and link present.
    $assert->elementAttributeContains('css', 'div.content-wrapper > a.ut-cta-link--external', 'tabindex', '-1');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive and expected image is present.
    $expected_path = 'utexas_image_style_340w_227h/public/image-test.png';
    $assert->elementAttributeContains('css', '.ut-flex-content-area .image-wrapper picture img', 'src', $expected_path);
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.ut-flex-content-area .image-wrapper a picture source');
    // Flex Content Area instance 2 is rendered.
    $assert->elementTextContains('css', '.ut-flex-content-area:nth-child(2) h3.ut-headline', 'Flex Content Area Headline 2');
    $assert->pageTextContains('Flex Content Area Copy 2');
    $assert->linkExists('Flex Content Area External Link 2');
    $assert->elementTextContains('css', '.ut-flex-content-area:nth-child(2) a.ut-btn', 'Flex Content Area Call to Action 2');
    // Verify that a FCA item with no CTA does not, in fact, render a CTA.
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) a.ut-btn');

    // Test rendering of YouTube video.
    $this->drupalGet('admin/content/block');
    $page->findLink($block_name)->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[0]->click();
    $page->pressButton('image-0-media-library-remove-button-field_block_fca-0-flex_content_area');
    $session->wait(3000);
    $this->assertTrue($assert->waitForText('One media item remaining.'));
    $session->wait(3000);
    $page->pressButton('Add media');
    $this->assertTrue($assert->waitForText('Add or select media'));
    $this->clickLink("Video (External)");
    $this->assertTrue($assert->waitForText('Add Video (External) via URL'));

    $assert->pageTextContains('Video 1');
    // Select the 1st video media item (should be "Video 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));
    $this->submitForm([], 'Save');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been updated.');

    $this->drupalGet('node/' . $flex_page_id);
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'src', "/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DvXyqBkXDacU");
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'width', "100%");
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'height', "100%");

    // The outer iframe has a title attribute.
    // See https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/1763.
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'title', "UT Drupal Kit 2.0 Intro and Demo");

    // The inner iframe has a title attribute.
    // See https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/1201.
    $inner_frame = 'frames[0].document.querySelector("iframe")';
    $this->assertSame('UT Drupal Kit 2.0 Intro and Demo', $session->evaluateScript("$inner_frame.getAttribute('title')"));

    // CRUD: UPDATE
    // Remove CTA title from Item 2, but leave CTA URL.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $this->clickDetailsBySummaryText('Flex Content Area 2 (Flex Content Area Headline 2)');
    $form->fillField('field_block_fca[1][flex_content_area][cta_wrapper][link][title]', '');
    // Save block.
    $form->pressButton('Save');

    // CRUD: READ.
    $this->drupalGet('node/' . $flex_page_id);
    // Flex Content Area instance 2 now does NOT render a CTA,
    // but the headline is still a link.
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(2) a.ut-btn');
    $assert->linkExists('Flex Content Area Headline 2');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * Verify multiple FCAs work as designed.
   */
  public function verifyFlexContentAreaMultiple() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $session = $this->getSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $session->getPage();

    // Block info.
    $block_type = 'Flex Content Area';
    $block_type_id = 'utexas_flex_content_area';
    $block_name = $block_type . ' Test';
    $block_content_create_form_id = 'block-content-utexas-flex-content-area-form';
    $block_content_edit_form_id = 'block-content-utexas-flex-content-area-edit-form';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Flex Content Area 1 fields.
    $this->clickDetailsBySummaryText('New item');
    $form->fillField('info[0][value]', $block_name);
    $form->fillField(('field_block_fca[0][flex_content_area][headline]'), 'Flex Content Area Headline 1');
    // Add Flex Content Area 2.
    // Note that because of alpha-order by Headline, this will become the LAST
    // Flex Content Area in the list after the block is saved.
    // Add Flex Content Area 3 and fill fields.
    $form->pressButton('Add another Flex Content Area item');
    $this->clickDetailsBySummaryText('New item', 2);
    $form->fillField(('field_block_fca[1][flex_content_area][headline]'), 'Flex Content Area Headline 2');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');

    // CRUD: READ
    // Verify Flex Content Area items can be removed.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $assert->pageTextContains('Flex Content Area Headline 2');

    // CRUD: UPDATE
    // Clear out the data for item 3.
    $this->clickDetailsBySummaryText('Flex Content Area 2 (Flex Content Area Headline 2)');
    $form->fillField(('field_block_fca[1][flex_content_area][headline]'), '');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD:READ.
    // Re-open block.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $this->waitForForm($block_content_edit_form_id);
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Flex Content Area Headline 2');

    // Test remove button.
    $form->pressButton('Remove item 1');
    // Verify that a user can cancel the removal.
    // Press "OK" on confirm remove modal.
    $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    // Item 1 is still present.
    $this->assertTrue($assert->waitForText('Flex Content Area Headline 1'));
    // Now verify a user can proceed with removal.
    $form->pressButton('Remove item 1');
    // Press "OK" on confirm remove modal.
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    $this->assertTrue($assert->waitForElementRemoved('css', '[data-drupal-selector="edit-field-block-fca-0-confirm-remove"]'));
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD:READ.
    // Re-open block.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $this->waitForForm($block_content_edit_form_id);
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Flex Content Area Headline 1');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
  }

}
