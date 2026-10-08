<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies Featured Highlight field schema & output.
 */
trait FeaturedHighlightTestTrait {

  /**
   * Test schema.
   */
  public function verifyFeaturedHighlight() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $session = $this->getSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $session->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Create custom media image.
    $mid = $this->createTestMediaImage('image-1000x1000.png');

    // Block info.
    $block_type = 'Featured Highlight';
    $block_type_id = 'utexas_featured_highlight';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Featured Highlight fields.
    // Open the media library.
    $session->wait(3000);
    $page->pressButton('Add media');
    $session->wait(3000);
    $this->assertTrue($assert->waitForText('Add or select media'));
    $assert->pageTextContains('1000x1000.png');
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    // Select the second media item (should be "1000x1000.png").
    $checkboxes[1]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));
    $form->fillField('field_block_featured_highlight[0][headline]', 'Featured Highlight Headline');
    $form->fillField('field_block_featured_highlight[0][copy][value]', '<h3>Heading text</h3><p>Featured Highlight copy</p>');
    $form->fillField('field_block_featured_highlight[0][cta_wrapper][link][uri]', 'https://featuredhighlight.test');
    $form->fillField('field_block_featured_highlight[0][cta_wrapper][link][title]', 'Featured Highlight Link');
    $form->fillField('field_block_featured_highlight[0][cta_wrapper][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_featured_highlight[0][cta_wrapper][link][options][attributes][class]', 'ut-cta-link--external');
    $form->fillField('field_block_featured_highlight[0][date]', '01-17-2019');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/' . $mid . '/edit/usage');
    $assert->pageTextContains('Content block: Featured Highlight');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementTextContains('css', 'h2.ut-headline a', 'Featured Highlight Headline');
    $assert->pageTextContains('Featured Highlight Copy');
    $assert->linkByHrefExists('https://featuredhighlight.test');
    $assert->pageTextContains('Jan. 17, 2019');
    // Verify responsive image is present.
    $assert->elementExists('css', '.utexas-featured-highlight .image-wrapper picture source');
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.utexas-featured-highlight .image-wrapper a picture source');
    // Verify expected image.
    $expected_path = 'utexas_image_style_600w/public/test_files/image-1000x1000';
    $assert->elementAttributeContains('css', '.utexas-featured-highlight .image-wrapper picture img', 'src', $expected_path);
    // // Verify link exists with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.utexas-featured-highlight .ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify CTA not tabbable when headline and link present.
    $assert->elementAttributeContains('css', '.ut-btn.ut-cta-link--external', 'tabindex', '-1');

    // CRUD: UPDATE
    // Remove CTA title but leave CTA URL.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $form->fillField('field_block_featured_highlight[0][cta_wrapper][link][title]', '');
    // Save block.
    $form->pressButton('Save');

    // CRUD: READ.
    $this->drupalGet('node/' . $flex_page_id);
    // Featured Highlight now does NOT render a CTA,
    // but the headline is still a link.
    $assert->elementNotExists('css', '.utexas-featured-highlight:nth-child(1) a.ut-btn');
    $assert->linkExists('Featured Highlight Headline');

    // CRUD: UPDATE
    // Set display to "Bluebonnet (Medium)".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_featured_highlight_2'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', 'div.utexas-featured-highlight.medium');
    // Verify headings in copy field are white.
    $medium_copy_color = $session->evaluateScript('jQuery(".utexas-featured-highlight.medium .ut-copy h3").css("color")');
    $this->assertSame("rgb(255, 255, 255)", $medium_copy_color);

    // CRUD: UPDATE
    // Set display to "Charcoal (Dark)".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_featured_highlight_3'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', 'div.utexas-featured-highlight.dark');
    // Verify headings in copy field are white.
    $dark_copy_color = $session->evaluateScript('jQuery(".utexas-featured-highlight.dark .ut-copy h3").css("color")');
    $this->assertSame("rgb(255, 255, 255)", $dark_copy_color);

    // CRUD: UPDATE
    // Images smaller than 500px aren't rendered via responsive picture src.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $page->pressButton('media-0-media-library-remove-button-field_block_featured_highlight-0');
    $this->assertTrue($assert->waitForText('One media item remaining.'));
    $page->pressButton('Add media');
    $session->wait(3000);
    $this->assertTrue($assert->waitForText('Add or select media'));
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    // Select the first media item.
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));
    $this->submitForm([], 'Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ
    // Verify that the background image style matches the uploaded image.
    $this->drupalGet('node/' . $flex_page_id);
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('xpath', '//div[contains(@class, "utexas-featured-highlight")]//img', 'src', $expected_path);
    // Verify that there is no <picture> element.
    $assert->elementNotExists('xpath', '//div[contains(@class, "utexas-featured-highlight")]//picture');

    // CRUD: UPDATE
    // Test rendering of YouTube video.
    $this->drupalGet('admin/content/block');
    $page->findLink($block_name)->click();
    $page->pressButton('media-0-media-library-remove-button-field_block_featured_highlight-0');
    $this->assertTrue($assert->waitForText('One media item remaining.'));
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

    // CRUD: READ
    // Verify video iframe details.
    $this->drupalGet('node/' . $flex_page_id);
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'src', "/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DvXyqBkXDacU");
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'width', "100%");
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'height', "100%");
    // The outer iframe has a title attribute.
    // See https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/1763.
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'title', "UT Drupal Kit 2.0 Intro and Demo");
    // The inner iframe has a title attribute.
    // See https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/1201.
    $inner_frame = 'frames[0].document.querySelector("iframe")';
    $this->assertSame(
      'UT Drupal Kit 2.0 Intro and Demo',
      $session->evaluateScript("$inner_frame.getAttribute('title')")
    );

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

}
