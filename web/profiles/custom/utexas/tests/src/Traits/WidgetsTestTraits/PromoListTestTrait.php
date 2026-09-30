<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Defines testing for Promo List widget.
 */
trait PromoListTestTrait {

  /**
   * Verify Promo List widget schema & output.
   */
  public function verifyPromoList() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Promo List';
    $block_type_id = 'utexas_promo_list';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Promo List 1 fields.
    $this->clickDetailsBySummaryText('New Promo List item');
    $this->addMediaLibraryImage();
    $form->fillField('field_block_pl[0][headline]', 'Promo List 1 Headline');
    // Fill Promo List 1 Item 1 fields.
    $form->fillField('field_block_pl[0][promo_list_items][items][0][details][item][item][headline]', 'List 1 item 1');
    $form->fillField('field_block_pl[0][promo_list_items][items][0][details][item][item][copy][value]', 'Copy text for list 1 item 1');
    $form->fillField('field_block_pl[0][promo_list_items][items][0][details][item][item][link][uri]', '/sites/default/files/file%20with%20spaces.pdf');
    $form->fillField('field_block_pl[0][promo_list_items][items][0][details][item][item][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_pl[0][promo_list_items][items][0][details][item][item][link][options][attributes][class]', 'ut-cta-link--external');
    // Add Promo List 1 Item 2 and fill fields.
    $form->pressButton('Add Promo List item');
    $this->clickDetailsBySummaryText('New Promo List item', 2);
    $form->fillField('field_block_pl[0][promo_list_items][items][1][details][item][item][headline]', 'List 1 item 2');
    // Add Promo List 2 and fill fields.
    $this->addDraggableFormItem($form, 'Add another item');
    $this->clickDetailsBySummaryText('New Promo List item', 3);
    $form->fillField('field_block_pl[1][headline]', 'Promo List 2 Headline');
    // Fill Promo List 2 Item 1 fields.
    $form->fillField('field_block_pl[1][promo_list_items][items][0][details][item][item][headline]', 'List 2 item 1');
    $form->fillField('field_block_pl[1][promo_list_items][items][0][details][item][item][copy][value]', 'Copy text for list 2 item 1');
    $form->fillField('field_block_pl[1][promo_list_items][items][0][details][item][item][link][uri]', '/node/' . $flex_page_id);
    $form->fillField('field_block_pl[1][promo_list_items][items][0][details][item][item][link][options][attributes][class]', 'ut-cta-link--lock');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/1/edit/usage');
    $assert->pageTextContains('Content block: Promo List');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Use weighting fields to reverse the order of Promo List items 1 & 2.
    $form->pressButton('Show row weights');
    $form->fillField('field_block_pl[0][promo_list_items][items][0][weight]', '1');
    $form->fillField('field_block_pl[0][promo_list_items][items][1][weight]', '0');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('node/' . $flex_page_id);
    // Promo List items 1 & 2 have been reordered.
    $assert->elementTextContains('css', '.block-block-content div.promo-list:nth-child(1) h3.ut-headline', 'List 1 item 2');
    $assert->elementTextContains('css', '.block-block-content div.promo-list:nth-child(2) h3.ut-headline', 'List 1 item 1');
    // Other input is present.
    $assert->elementTextContains('css', '.block-block-content div div:nth-child(1) h3.ut-headline--underline', 'Promo List 1 Headline');
    $assert->elementTextContains('css', '.block-block-content div div:nth-child(2) h3.ut-headline--underline', 'Promo List 2 Headline');
    $assert->pageTextContains('Copy text for list 1 item 1');
    $assert->pageTextContains('Copy text for list 2 item 1');
    $assert->linkByHrefExists('test-flex-page');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.utexas-promo-list-container .ut-cta-link--external', 'rel', 'noopener noreferrer');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive image is present.
    $assert->elementExists('css', '.ut-promo-list-wrapper .promo-list:nth-child(2) picture source');
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.ut-promo-list-wrapper .promo-list:nth-child(2) a picture source');
    // Verify expected image.
    $expected_path = 'utexas_image_style_64w_64h/public/image-test.png';
    $assert->elementAttributeContains('css', '.ut-promo-list-wrapper .promo-list:nth-child(2) picture img', 'src', $expected_path);

    // CRUD: UPDATE
    // Set display to "Responsive".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_list_2'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', 'div.ut-promo-list-wrapper.two-column-responsive');

    // CRUD: UPDATE
    // Set display to "Two Columns".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_list_3'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', 'div.ut-promo-list-wrapper.two-side-by-side');

    // CRUD: UPDATE
    // Set display to "Stacked".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_list_4'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display > div.utexas-promo-list-container > div.ut-promo-list-wrapper');

    // CRUD: UPDATE
    // Re-set row weight for subsequent tests.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $form->pressButton('Hide row weights');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * Verify multiple Promo List items work as expected.
   */
  public function verifyPromoListMultiple() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Block info.
    $block_type = 'Promo List';
    $block_type_id = 'utexas_promo_list';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Promo List 1 fields.
    $this->clickDetailsBySummaryText('New Promo List item');
    $this->addMediaLibraryImage();
    $form->fillField('field_block_pl[0][headline]', 'Promo List 1 Headline');
    // Fill Promo List 1 Item 1 fields.
    $form->fillField('field_block_pl[0][promo_list_items][items][0][details][item][item][headline]', 'List 1 item 1');
    // Add Promo List 1 Item 2 but leave blank.
    $form->pressButton('Add Promo List item');
    $this->clickDetailsBySummaryText('New Promo List item', 2);
    // Add Promo List 1 Item 3 and fill fields.
    $form->pressButton('Add Promo List item');
    $this->clickDetailsBySummaryText('New Promo List item', 3);
    $form->fillField('field_block_pl[0][promo_list_items][items][2][details][item][item][headline]', 'List 1 item 3');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_pl[0][promo_list_items][items][1][details][item][item][headline]', 'List 1 item 3');
    $form->pressButton('Save');

    // CRUD: UPDATE
    // Clear out the data for item 2.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $form->pressButton('Remove item 2');
    // Press "OK" on confirm remove modal.
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    $this->assertTrue($assert->waitForElementRemoved('css', '[data-drupal-selector="edit-field-block-pl-0-promo-list-items-items-1-actions-confirm-remove"]'));
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Verify that text has been removed.
    $assert->pageTextNotContains('List 1 item 3');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
  }

}
