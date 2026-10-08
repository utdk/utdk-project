<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies Resource schema & validation.
 */
trait ResourcesTestTrait {

  /**
   * Verify Resources widget schema & output.
   */
  public function verifyResources() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Resources';
    $block_type_id = 'utexas_resources';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Resource Collection fields.
    $form->fillField('field_block_resources[0][headline]', 'Resource Container Headline');
    // Fill Resource Collection items[0] fields.
    $this->clickDetailsBySummaryText('New Resource item');
    $this->addMediaLibraryImage();
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][headline]', 'Resource 1 Headline');
    // Fill Resource Collection items[0] (Resource 1) links[0] fields.
    $this->clickDetailsBySummaryText('New Link');
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][0][uri]', 'https://resource.test');
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][0][title]', 'Resource External Link');
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][0][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][0][options][attributes][class]', 'ut-cta-link--external');
    // Add Resource Collection items[0] (Resource 1) links[1] and fill fields.
    // 'field_block_resources00' corresponds to the "Add link" button for the
    // first resource item.
    $this->clickElementByName($form, 'field_block_resources00');
    $this->clickDetailsBySummaryText('New Link', 2);
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][0][details][item][item][links][1][uri]"]'));
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][1][uri]', '/node/' . $flex_page_id);
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][1][title]', 'Resource Internal Link');
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][1][options][attributes][class]', 'ut-cta-link--lock');
    // Add Resource Collection items[1] (Resource 2) and fill fields.
    $form->pressButton('Add another Resource item');
    $this->assertTrue($assert->waitForText('New Resource item'));
    // Expand the *second* container (as indicated by index 2).
    $this->clickDetailsBySummaryText('New Resource item', 2);
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][1][details][item][item][headline]"]'));
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][headline]', 'Resource 2 Headline');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/1/edit/usage');
    $assert->pageTextContains('Content block: Resources');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Reverse the order of Resource Collection items[0] (Resource 1) & items[1]
    // (Resource 2).
    $form->pressButton('Show row weights');
    $form->fillField('field_block_resources[0][resource_items][items][0][weight]', 1);
    $form->fillField('field_block_resources[0][resource_items][items][1][weight]', 0);
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('node/' . $flex_page_id);
    // Verify page output.
    $assert->elementTextContains('css', 'h3.ut-headline--underline', 'Resource Container Headline');
    // User-supplied weighting of resource items is respected.
    $assert->elementTextContains('xpath', '//div[@class="utexas-resource"][1]', 'Resource 2 Headline');
    $assert->elementTextContains('xpath', '//div[@class="utexas-resource"][2]', 'Resource 1 Headline');
    $assert->pageTextContains('Resource Internal Link');
    $assert->linkByHrefExists('https://resource.test');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-resources-wrapper .ut-cta-link--external', 'rel', 'noopener noreferrer');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive and expect image is present.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_400w_250h/public/image-test';
    $assert->elementAttributeContains('css', '.utexas-resource .image-wrapper picture img', 'src', $expected_path);
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.utexas-resource .image-wrapper a picture source');

    // CRUD: UPDATE
    // Verify stacked display adds class to markup.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_resources_2'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display div.ut-resources-wrapper');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Add Resource Collection items[1] (Resource 1) links[2] and fill fields.
    $form->fillField('field_block_resources[0][resource_items][items][0][weight]', 1);
    $form->fillField('field_block_resources[0][resource_items][items][1][weight]', 0);
    $this->clickDetailsBySummaryText('(Resource 1 Headline)');
    // Reverse the order of Resource Collection items[0] (Resource 2) & items[1]
    // (Resource 1).
    $this->clickDetailsBySummaryText('Link (Resource Internal Link)');
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][1][details][item][item][links][1][uri]"]'));
    // Remove data for the items[1] (Resource 1) links[1] link.
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][1][uri]', '');
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][1][title]', '');
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][1][options][attributes][class]', '0');
    // 'field_block_resources01' corresponds to the "Add link" button for the
    // second resource item.
    $this->clickElementByName($form, 'field_block_resources01');
    $this->clickDetailsBySummaryText('New Link', 2);
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][2][uri]', 'https://thirdlink.test');
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][2][title]', 'Third link');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ
    // (#1121: Verify links can be removed without loss of data.)
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $this->waitForForm($block_content_edit_form_id);
    $this->clickDetailsBySummaryText('(Resource 1 Headline)');
    // Confirm second link has data from third link previously created.
    // **** @todo convert to xpath as above in line 73 *****.
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][0][details][item][item][links][1][title]', 'Third link');
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][0][details][item][item][links][1][uri]', 'https://thirdlink.test');
    // Verify data for removed link is not present.
    $this->clickDetailsBySummaryText('(Resource 2 Headline)');
    $assert->pageTextNotContains('Resource Internal Link');

    // CRUD: UPDATE
    // Verify Resource collections can be removed.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Clear out the data for (Resource 2).
    $form->pressButton('Remove item 2');
    // Press "OK" on confirm remove modal.
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    $this->assertTrue($assert->waitForElementRemoved('css', '[data-drupal-selector="edit-field-block-resources-0-resource-items-items-1-actions-confirm-remove"]'));
    // Add Resource (3) and fill fields.
    $form->pressButton('Add another Resource item');
    $this->assertTrue($assert->waitForText('New Resource item'));
    $this->clickDetailsBySummaryText('New Resource item');
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][2][details][item][item][headline]"]'));
    $form->fillField('field_block_resources[0][resource_items][items][2][details][item][item][headline]', 'Resource 3 Headline');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ
    // View the block form.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $this->clickDetailsBySummaryText('(Resource 3 Headline)');
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][1][details][item][item][headline]', 'Resource 3 Headline');
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Resource 2 Headline');

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
   * Verify Resources links within collections are always displayed.
   */
  public function verifyResourcesMultiple() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Block info.
    $block_type = 'Resources';
    $block_type_id = 'utexas_resources';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';
    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Resource Collection Item 1 Link 1 fields.
    $this->clickDetailsBySummaryText('New Resource item');
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][0][details][item][item][links][0][title]"]'));
    $this->clickDetailsBySummaryText('New Link');
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][0][title]', 'Link 1');
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][0][uri]', 'https://resource.test');
    // Fill Resource Collection Item 1 fields.
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][headline]', 'Resource 1 Headline');
    // Add Resource Collection Item 1 Link 2 and fill fields.
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElement('css', '.utexas-link-form-element'));
    $this->clickDetailsBySummaryText('New Link', 2);
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][0][details][item][item][links][1][uri]"]'));
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][1][title]', 'Link 2');
    $form->fillField('field_block_resources[0][resource_items][items][0][details][item][item][links][1][uri]', 'https://resource2.test');
    // Add Resource Collection Item 2.
    $form->pressButton('Save');
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $form->pressButton('Add another Resource item');
    $this->assertTrue($assert->waitForText('New Resource item'));
    $this->clickDetailsBySummaryText('New Resource item', 1);
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][1][details][item][item][headline]"]'));
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][headline]', 'Resource 2 Headline');

    // Fill Resource Collection Item 2 Link 1 fields.
    $this->clickDetailsBySummaryText('New Link');
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][0][uri]', 'https://resource.test');
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][0][title]', 'Link 3');
    // Add Resource Collection Item 2 Link 2 and fill fields.
    // 'field_block_resources01' corresponds to the "Add link" button for the
    // second resource item.
    $this->clickElementByName($form, 'field_block_resources01');
    $this->clickDetailsBySummaryText('New Link', 2);
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][1][details][item][item][links][1][uri]"]'));
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][1][title]', 'Link 4');
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][1][uri]', 'https://resource2.test');
    // Add Resource Collection Item 2 Link 3 and fill fields.
    // 'field_block_resources01' corresponds to the "Add link" button for the
    // second resource item.
    $this->clickElementByName($form, 'field_block_resources01');
    $this->clickDetailsBySummaryText('New Link', 3);
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_resources[0][resource_items][items][1][details][item][item][links][2][uri]"]'));
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][2][title]', 'Link 5');
    $form->fillField('field_block_resources[0][resource_items][items][1][details][item][item][links][2][uri]', '/');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');
    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    $this->clickDetailsBySummaryText('(Resource 1 Headline)');
    $this->clickDetailsBySummaryText('(Resource 2 Headline)');
    // Verify there are 5 links between both collections, no missing links.
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][0][details][item][item][links][0][title]', 'Link 1');
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][0][details][item][item][links][1][title]', 'Link 2');
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][1][details][item][item][links][0][title]', 'Link 3');
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][1][details][item][item][links][1][title]', 'Link 4');
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][1][details][item][item][links][2][title]', 'Link 5');

    $this->removeBlocks([$block_name]);
  }

}
