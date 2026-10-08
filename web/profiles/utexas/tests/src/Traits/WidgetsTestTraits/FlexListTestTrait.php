<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Test input/output of Flex List field type via the Flex List block type.
 */
trait FlexListTestTrait {

  /**
   * Test schema.
   */
  public function verifyFlexList() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Flex List';
    $block_type_id = 'utexas_flex_list';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);

    $this->clickDetailsBySummaryText('New item');

    // Fill Flex List Item 1 fields.
    $form->fillField('field_utexas_flex_list_items[0][utexas_flex_list][header]', 'Location');
    $form->fillField('field_utexas_flex_list_items[0][utexas_flex_list][content][format]', 'restricted_html');
    $form->fillField('field_utexas_flex_list_items[0][utexas_flex_list][content][value]', 'FAC 326');

    $form->pressButton('Add another Flex list item');
    // $this->assertTrue($assert->waitForId('New item'));
    $this->clickDetailsBySummaryText('New item', 2);

    // Add Flex List Item 2 and fill fields.
    $form->fillField('field_utexas_flex_list_items[1][utexas_flex_list][content][format]', 'restricted_html');
    $form->fillField('field_utexas_flex_list_items[1][utexas_flex_list][header]', 'Website');
    $form->fillField('field_utexas_flex_list_items[1][utexas_flex_list][content][value]', 'https://drupalkit.its.utexas.edu');

    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->linkByHrefExists('https://drupalkit.its.utexas.edu');
    $assert->elementExists('css', '.utexas-flex-list.formatter-default dl dt.ut-headline');

    // CRUD: UPDATE
    // Set display to "Accordions".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_flex_list__accordion'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.utexas-flex-list.formatter-accordion details summary');

    // CRUD: UPDATE
    // Set display to "Horizontal Tabs".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_flex_list__horizontal_tabs'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.utexas-flex-list.formatter-htabs ul.nav-tabs li.nav-item a.active');

    // REMOVE.
    $this->drupalGet('/admin/content/block/');
    $this->getSession()->getPage()->findLink('Flex List Test')->click();
    $form = $this->waitForForm('block-content-utexas-flex-list-edit-form');
    $form->pressButton('Remove item 1');
    // Press "OK" on confirm remove modal.
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    $this->assertTrue($assert->waitForElementRemoved('css', '[data-drupal-selector="edit-field-utexas-flex-list-items-0-confirm-remove"]'));
    $form->pressButton('Save');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

}
