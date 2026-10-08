<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Defines testing for Promo Unit widget.
 */
trait PromoUnitTestTrait {

  /**
   * Verify promo unit widget schema & output.
   */
  public function verifyPromoUnit() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Promo Unit';
    $block_type_id = 'utexas_promo_unit';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Promo Unit fields.
    $form->fillField('field_block_pu[0][headline]', 'Promo Unit Container Headline');
    // Fill Promo Unit Item 1 fields.
    $this->clickDetailsBySummaryText('New Promo Unit item');
    $this->addMediaLibraryImage();
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][details][item][item][headline]', 'Promo Unit 1 Headline');
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][details][item][item][copy][value]', 'Promo Unit 1 Copy');
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][details][item][item][link][uri]', 'https://promounit.test');
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][details][item][item][link][title]', 'Promo Unit External Link');
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][details][item][item][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][details][item][item][link][options][attributes][class]', 'ut-cta-link--external');
    // Add Promo Unit Item 2 and fill fields.
    $form->pressButton('Add another Promo Unit item');
    $this->assertNotEmpty($assert->waitForElement('css', '[data-drupal-selector="edit-field-block-pu-0-promo-unit-items-items-1-details-item"]'));
    // Expand the *second* container (as indicated by index 2).
    $this->clickDetailsBySummaryText('New Promo Unit item', 2);
    $form->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][item][headline]', 'Promo Unit 2 Headline');
    $form->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][item][copy][value]', 'Promo Unit 2 Copy');
    $form->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][item][link][uri]', '/node/' . $flex_page_id);
    $form->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][item][link][title]', 'Promo Unit Internal Link');
    $form->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][item][link][options][attributes][class]', 'ut-cta-link--lock');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/1/edit/usage');
    $assert->pageTextContains('Content block: Promo Unit');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Use weighting fields to reverse the order of Promo Unit items 1 & 2.
    $form->pressButton('Show row weights');
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][weight]', 1);
    $form->fillField('field_block_pu[0][promo_unit_items][items][1][weight]', 0);
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('node/' . $flex_page_id);
    // Verify page output.
    $assert->elementTextContains('css', 'h3.ut-headline--underline', 'Promo Unit Container Headline');
    // User-supplied weighting of resource items is respected.
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(2) h3.ut-headline', 'Promo Unit 1 Headline');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(1) h3.ut-headline', 'Promo Unit 2 Headline');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(2)', 'Promo Unit 1 Copy');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(1)', 'Promo Unit 2 Copy');
    $assert->linkByHrefExists('https://promounit.test');
    $assert->linkByHrefExists('test-flex-page');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.utexas-promo-unit-container .ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify CTA not tabbable when headline and link present.
    $assert->elementAttributeContains('css', 'div > a.ut-cta-link--external', 'tabindex', '-1');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive image is present.
    $assert->elementExists('css', '.utexas-promo-unit:nth-child(2) picture source');
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.utexas-promo-unit:nth-child(2) a picture source');
    // Verify expected image.
    $expected_path = 'utexas_image_style_800w_500h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit:nth-child(2) picture img', 'src', $expected_path);

    // CRUD: UPDATE
    // Set display to "Responsive".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_unit_2'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $expected_path = 'utexas_image_style_800w_1000h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);

    // CRUD: UPDATE
    // Set display to "Square".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_unit_3'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $expected_path = 'utexas_image_style_800w_800h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);

    // CRUD: UPDATE
    // Set display to "Landscape Stacked".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_unit_4'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $expected_path = 'utexas_image_style_800w_500h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

    // CRUD: UPDATE
    // Set display to "Portrait Stacked".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_unit_5'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $expected_path = 'utexas_image_style_800w_1000h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

    // CRUD: UPDATE
    // Set display to "Square Stacked".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_promo_unit_6'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $expected_path = 'utexas_image_style_800w_800h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

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
   * Verify multiple promo unit items work as designed.
   */
  public function verifyPromoUnitMultiple() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Block info.
    $block_type = 'Promo Unit';
    $block_type_id = 'utexas_promo_unit';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Promo Unit fields.
    $form->fillField('field_block_pu[0][headline]', 'Promo Unit Container Headline');
    // Fill Promo Unit Item 1 fields.
    $this->clickDetailsBySummaryText('New Promo Unit item');
    $form->fillField('field_block_pu[0][promo_unit_items][items][0][details][item][item][headline]', 'Promo Unit 1 Headline');
    // Add Promo Unit Item 2 but leave blank.
    $form->pressButton('Add another Promo Unit item');
    $this->clickDetailsBySummaryText('New Promo Unit item', 2);
    // Add Promo Unit Item 3 and fill fields.
    $form->pressButton('Add another Promo Unit item');
    $this->clickDetailsBySummaryText('New Promo Unit item', 3);
    $form->fillField('field_block_pu[0][promo_unit_items][items][2][details][item][item][headline]', 'Promo Unit 3 Headline');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_pu[0][promo_unit_items][items][1][details][item][item][headline]', 'Promo Unit 3 Headline');

    // CRUD: UPDATE
    // Clear out the data for item 2.
    $form->pressButton('Remove item 2');
    // Press "OK" on confirm remove modal.
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    $this->assertTrue($assert->waitForElementRemoved('css', '[data-drupal-selector="edit-field-block-pu-0-promo-unit-items-items-1-actions-confirm-remove"]'));
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Verify that text has been removed.
    $assert->pageTextNotContains('Promo Unit 3 Headline');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
  }

}
