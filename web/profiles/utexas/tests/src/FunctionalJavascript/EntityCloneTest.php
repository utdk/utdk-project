<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\Tests\Traits\Core\CronRunTrait;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Demonstrate that various node types can be cloned.
 */
#[RunTestsInSeparateProcesses]
class EntityCloneTest extends FunctionalJavascriptTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testSiteManagerUser);
  }

  /**
   * Clone a Flex Page.
   */
  public function testFlexPage() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $session = $this->getSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $session->getPage();

    // Create flex page.
    $flex_page_id_original = $this->createFlexPage();

    // Reusable Block info.
    $block_type_reusable = 'Featured Highlight';
    $block_name_reusable = 'Reusable block test';
    $block_type_id_reusable = 'utexas_featured_highlight';
    $block_plugin_id_reusable = str_replace('_', '-', $block_type_id_reusable);
    $block_content_create_form_id_reusable = 'block-content-' . $block_plugin_id_reusable . '-form';
    $block_content_edit_form_id_reusable = 'block-content-' . $block_plugin_id_reusable . '-edit-form';

    // Inline block info.
    $block_type_inline = 'Featured Highlight';
    $block_name_inline = 'Inline block test';
    $block_plugin_id_inline = 'utexas-featured-highlight';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id_reusable);
    $form = $this->waitForForm($block_content_create_form_id_reusable);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name_reusable);
    // Fill Featured Highlight fields.
    $form->fillField('field_block_featured_highlight[0][headline]', 'Reusable block original');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type_reusable . ' ' . $block_name_reusable . ' has been created.');
    // Place the reusable block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id_original);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name_reusable);
    $this->savePageLayout();
    // Place an inline block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id_original);
    $form_values = [
      'settings[label]' => $block_name_inline,
      'settings[block_form][field_block_featured_highlight][0][headline]' => 'Inline block original',
    ];
    $this->createInlineBlockOnFlexPage($block_type_inline, $form_values);
    $this->savePageLayout();

    // CRUD: UPDATE
    // Make a revision to the ORIGINAL inline block.
    $this->drupalGetNodeLayoutTab($flex_page_id_original);
    $form_values = ['settings[block_form][field_block_featured_highlight][0][headline]' => 'Inline block first revision'];
    $this->updateBlockOnFlexPage($block_name_inline, $form_values, $block_plugin_id_inline, 2);
    $this->savePageLayout();

    // CRUD: CREATE
    // Clone the node.
    $this->drupalGet('entity_clone/node/' . $flex_page_id_original);
    $page->pressButton('Clone');
    $flex_page_id_clone = $flex_page_id_original + 1;

    // CRUD: UPDATE
    // Make a revision to the CLONE inline block.
    $this->drupalGetNodeLayoutTab($flex_page_id_clone);
    $form_values = ['settings[block_form][field_block_featured_highlight][0][headline]' => 'Inline block revision to clone'];
    $this->updateBlockOnFlexPage($block_name_inline, $form_values, $block_plugin_id_inline, 2);
    $this->savePageLayout();

    // CRUD: UPDATE
    // Make a revision to the ORIGINAL inline block.
    $this->drupalGetNodeLayoutTab($flex_page_id_original);
    $form_values = ['settings[block_form][field_block_featured_highlight][0][headline]' => 'Inline block revision to original'];
    $this->updateBlockOnFlexPage($block_name_inline, $form_values, $block_plugin_id_inline, 2);
    $this->savePageLayout();

    // CRUD: READ
    // Verify changes to ORIGINAL inline block do not effect CLONE inline block.
    $this->drupalGet('node/' . $flex_page_id_clone);
    $this->assertTrue($assert->waitForText('Inline block revision to clone'));

    // CRUD: UPDATE
    // Update the reusable block.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name_reusable);
    $form = $this->waitForForm($block_content_edit_form_id_reusable);
    // Fill Featured Highlight fields.
    $form->fillField('field_block_featured_highlight[0][headline]', 'Reusable block revision');
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type_reusable . ' ' . $block_name_reusable . ' has been updated.');

    // CRUD: READ
    // Confirm that the CLONE and ORIGINAL node both display the same revision
    // of the reusable block.
    $this->drupalGet('node/' . $flex_page_id_original);
    $this->assertTrue($assert->waitForText('Reusable block revision'));
    $this->drupalGet('node/' . $flex_page_id_clone);
    $this->assertTrue($assert->waitForText('Reusable block revision'));

    // CRUD: DELETE.
    // Delete the original node.
    $this->removeNodes([$flex_page_id_original]);

    // CRUD: READ
    // Inline block from clone source exists BEFORE cron run.
    $this->drupalGet('node/' . $flex_page_id_clone);
    $assert->pageTextContains('Inline block revision to clone');

    // CRUD: READ
    // Inline block from clone source exists AFTER cron run.
    $this->cronRun();
    $this->drupalGet('node/' . $flex_page_id_clone);
    $assert->pageTextContains('Inline block revision to clone');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name_reusable]);
    $this->removeNodes([$flex_page_id_clone]);
  }

}
