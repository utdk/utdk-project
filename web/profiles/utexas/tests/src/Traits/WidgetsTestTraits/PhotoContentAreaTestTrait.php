<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies Photo Content Area field schema, validation, & output.
 */
trait PhotoContentAreaTestTrait {

  /**
   * Test schema.
   */
  public function verifyPhotoContentArea() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Photo Content Area';
    $block_type_id = 'utexas_photo_content_area';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Photo Content Area fields.
    $this->addMediaLibraryImage();
    $form->fillField('field_block_pca[0][photo_credit]', 'Photo Content Area Photo Credit');
    $form->fillField('field_block_pca[0][headline]', 'Photo Content Area Headline');
    $form->fillField('field_block_pca[0][copy][value]', 'Photo Content Area Copy');
    // Fill Link 1 fields.
    $this->clickDetailsBySummaryText('New Link');
    $form->fillField('field_block_pca[0][links][0][uri]', 'https://photocontentarea.test');
    $form->fillField('field_block_pca[0][links][0][title]', 'Photo Content Area Link');
    $form->fillField('field_block_pca[0][links][0][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_pca[0][links][0][options][attributes][class]', 'ut-cta-link--external');
    // Add Link 2 and fill fields.
    $page->pressButton('Add link');
    $this->clickDetailsBySummaryText('New Link', 2);
    $form->fillField('field_block_pca[0][links][1][uri]', 'https://second.test');
    $form->fillField('field_block_pca[0][links][1][title]', 'Photo Content Area Second Link');
    // Add Link 3 and fill fields.
    $page->pressButton('Add link');
    $this->clickDetailsBySummaryText('New Link', 3);
    $form->fillField('field_block_pca[0][links][2][uri]', 'https://third.test');
    $form->fillField('field_block_pca[0][links][2][title]', 'Photo Content Area Third Link');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/1/edit/usage');
    $assert->pageTextContains('Content block: Photo Content Area');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementTextContains('css', 'div.caption span', 'Photo Content Area Photo Credit');
    $assert->elementTextContains('css', '.ut-photo-content-area h2.ut-headline', 'Photo Content Area Headline');
    $assert->pageTextContains('Photo Content Area Copy');
    $assert->linkByHrefExists('https://photocontentarea.test');
    $assert->linkByHrefExists('https://second.test');
    $assert->linkByHrefExists('https://third.test');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-photo-content-area .ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify responsive image is present.
    $expected_path = 'utexas_image_style_450w_600h/public/image-test';
    $assert->elementAttributeContains('css', '.photo-wrapper picture img', 'src', $expected_path);

    // CRUD: UPDATE
    // Verify stacked display formatter adding class to markup.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_photo_content_area_2'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display div.ut-photo-content-area');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Remove data for the second link
    // (#1121: Verify links can be removed without loss of data.)
    $this->clickDetailsBySummaryText('Link (Photo Content Area Second Link)');
    $form->fillField('field_block_pca[0][links][1][uri]', '');
    $form->fillField('field_block_pca[0][links][1][title]', '');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $assert->fieldValueEquals('field_block_pca[0][links][1][title]', 'Photo Content Area Third Link');
    $assert->fieldValueEquals('field_block_pca[0][links][1][uri]', 'https://third.test');
    // Verify data for removed link is not present.
    $assert->pageTextNotContains('https://second.test');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

}
