<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies that Flex Pages can be revised in a Drupal way.
 */
trait FlexPageRevisionsTestTrait {

  /**
   * Test that revisioning works per Drupal convention.
   */
  public function verifyFlexPageRevisions() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Featured Highlight';
    $block_name = $block_type . ' Test';
    $block_plugin_id = 'utexas-featured-highlight';

    // CRUD: CREATE
    // Place an inline block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = [
      'settings[label]' => $block_name,
      'settings[block_form][field_block_featured_highlight][0][headline]' => 'Revision 1',
    ];
    $this->createInlineBlockOnFlexPage($block_type, $form_values);
    $this->savePageLayout();

    // CRUD: UPDATE
    // Make a revision to the inline block.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[block_form][field_block_featured_highlight][0][headline]' => 'Revision 2'];
    $this->updateBlockOnFlexPage($block_name, $form_values, $block_plugin_id);
    $this->savePageLayout();

    // CRUD: UPDATE
    // Revert to first revision.
    $this->drupalGet('node/' . $flex_page_id . '/revisions');
    $this->scrollLinkIntoViewAndClick($page, 'Revert');
    $form = $this->waitForForm('node-revision-revert-confirm');
    $this->clickInputByValue($form, 'Revert');

    // CRUD: READ
    // The page has "Revision 1" again (the revert worked).
    $this->drupalGet('node/' . $flex_page_id);
    $assert->pageTextContains('Revision 1');

    // CRUD: UPDATE
    // Make another revision to the inline block.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[block_form][field_block_featured_highlight][0][headline]' => 'Revision 3'];
    $this->updateBlockOnFlexPage($block_name, $form_values, $block_plugin_id);
    $this->savePageLayout();

    // CRUD: READ
    // The page has "Revision 3" now.
    $this->drupalGet('node/' . $flex_page_id);
    $assert->pageTextContains('Revision 3');

    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

}
