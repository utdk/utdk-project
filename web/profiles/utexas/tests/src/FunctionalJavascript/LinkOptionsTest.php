<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies custom link options behavior.
 */
#[RunTestsInSeparateProcesses]
class LinkOptionsTest extends FunctionalJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testSiteManagerUser);
  }

  /**
   * Test URL variations to make sure they're processed correctly.
   */
  public function testVerifyUrls() {
    // CRUD: CREATE
    // Create flex page.
    $flex_page_id = $this->createFlexPage();
    $page = $this->getSession()->getPage();

    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Block info.
    $block_type = 'Quick Links';
    $block_type_id = 'utexas_quick_links';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_name = 'Link URLs Test';
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Link 1 fields.
    $this->clickDetailsBySummaryText('New link item');
    $form->fillField('field_block_ql[0][quick_links_items][items][0][details][item][item][title]', 'Internal link with anchor');
    $form->fillField('field_block_ql[0][quick_links_items][items][0][details][item][item][uri]', '/node/' . $flex_page_id . '#anchor');

    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);

    // Add Link 2 and fill fields.
    $form->pressButton('Add item');
    $this->assertTrue($assert->waitForText('New link item'));
    $this->clickDetailsBySummaryText('New link item');
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_ql[0][quick_links_items][items][1][details][item][item][title]"]'));
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][title]', 'Internal link with query');
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][uri]', '/node/' . $flex_page_id . '?query=1&search=test');

    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);

    // Add Link 3 and fill fields.
    $form->pressButton('Add item');
    $this->assertTrue($assert->waitForText('New link item'));
    $this->clickDetailsBySummaryText('New link item');
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_ql[0][quick_links_items][items][2][details][item][item][title]"]'));
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][title]', 'Link to front with query and anchor');
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][uri]', '/#anchor?query=1&search=test');

    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Delta 0 is present, links to an external site, shows an external icon,
    // and opens in a new tab.
    $assert->responseContains('<a href="/test-flex-page#anchor" class="ut-link" data-once="link">Internal link with anchor</a>');
    // Delta 1 is present, links to an internal page, and has a lock.
    $assert->responseContains('<a href="/test-flex-page?query=1&amp;search=test" class="ut-link" data-once="link">Internal link with query</a>');
    // Delta 2 is present, links to the front page, and has a caret.
    $assert->responseContains('<a href="/#anchor?query=1&amp;search=test" class="ut-link" data-once="link">Link to front with query and anchor</a>');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * Test link icons provided by the UTexasLinkOptions widget.
   */
  public function testVerifyIcons() {
    // CRUD: CREATE
    // Create flex page.
    $flex_page_id = $this->createFlexPage();

    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Block info.
    $block_type = 'Quick Links';
    $block_type_id = 'utexas_quick_links';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_name = 'Link Options Test';
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Quick Links fields.
    $form->fillField('field_block_ql[0][headline]', 'Quick Links Headline');
    // Fill Quick Links links[0] fields.
    $this->clickDetailsBySummaryText('New link item');
    $form->fillField('field_block_ql[0][quick_links_items][items][0][details][item][item][title]', 'Link Number 1');
    $form->fillField('field_block_ql[0][quick_links_items][items][0][details][item][item][uri]', 'https://www.utexas.edu');
    $form->checkField('field_block_ql[0][quick_links_items][items][0][details][item][item][options][attributes][target][_blank]');
    $form->fillField('field_block_ql[0][quick_links_items][items][0][details][item][item][options][attributes][class]', 'ut-cta-link--external');

    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);

    // Add Quick Links links[1] and fill fields.
    $form->pressButton('Add item');
    $this->assertTrue($assert->waitForText('New link item'));
    $this->clickDetailsBySummaryText('New link item');
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_ql[0][quick_links_items][items][1][details][item][item][title]"]'));
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][title]', 'Link Number 2');
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][uri]', '/node/' . $flex_page_id);
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][options][attributes][class]', 'ut-cta-link--lock');

    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);

    // Add Quick Links links[2] and fill fields.
    $form->pressButton('Add item');
    $this->assertTrue($assert->waitForText('New link item'));
    $this->clickDetailsBySummaryText('New link item');
    $this->assertNotEmpty($assert->waitForElement('css', 'input[name="field_block_ql[0][quick_links_items][items][2][details][item][item][title]"]'));
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][title]', 'Link Number 3');
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][uri]', '<front>');
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][options][attributes][class]', 'ut-cta-link--angle-right');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Delta 0 is present, links to an external site, shows an external icon,
    // and opens in a new tab.
    $assert->responseContains('<a href="https://www.utexas.edu" target="_blank" class="ut-cta-link--external ut-link" rel="noopener noreferrer" data-once="link" aria-label="Link Number 1; external link; opens in new window">Link Number 1</a>');
    // Delta 1 is present, links to an internal page, and has a lock.
    $assert->responseContains('<a href="/test-flex-page" class="ut-cta-link--lock ut-link" data-once="link" aria-label="Link Number 2; restricted link">Link Number 2</a>');
    // Delta 2 is present, links to the front page, and has a caret.
    $assert->responseContains('<a href="/" class="ut-cta-link--angle-right ut-link" data-once="link">Link Number 3</a>');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Fill Quick Links links[2] fields.
    $this->clickDetailsBySummaryText('Item 3 (Link Number 3)');
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][uri]', 'https://quicklinks.test');
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][title]', 'Updated third link');
    $form->fillField('field_block_ql[0][quick_links_items][items][2][details][item][item][options][attributes][class]', '0');
    // Empty Quick Links links[1] fields.
    $this->clickDetailsBySummaryText('Item 2 (Link Number 2)');
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][uri]', '');
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][title]', '');
    $form->fillField('field_block_ql[0][quick_links_items][items][1][details][item][item][options][attributes][class]', '0');
    $form->uncheckField('field_block_ql[0][quick_links_items][items][1][details][item][item][options][attributes][target][_blank]');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $this->waitForForm($block_content_edit_form_id);
    // Confirm second link has data from third link previously created.
    $this->clickDetailsBySummaryText('Item 2 (Updated third link)');
    $assert->fieldValueEquals('field_block_ql[0][quick_links_items][items][1][details][item][item][title]', 'Updated third link');
    $assert->fieldValueEquals('field_block_ql[0][quick_links_items][items][1][details][item][item][uri]', 'https://quicklinks.test');
    $assert->fieldValueEquals('field_block_ql[0][quick_links_items][items][1][details][item][item][options][attributes][class]', '0');
    // Assert former second link is now gone.
    $assert->pageTextNotContains('Link Number 2');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

}
