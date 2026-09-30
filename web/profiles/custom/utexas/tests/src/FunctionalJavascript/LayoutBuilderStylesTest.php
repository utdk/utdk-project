<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies default Layout Builder Styles are present & add expected classes.
 */
#[RunTestsInSeparateProcesses]
class LayoutBuilderStylesTest extends FunctionalJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testSiteManagerUser);
  }

  /**
   * Test any custom widgets sequentially, using the same installation.
   */
  public function testStyles() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    // Create flex page.
    $flex_page_id = $this->createFlexPage();

    // CRUD: READ
    // The default layout is twocol 50/50.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $assert->elementExists('css', '.utexas-layout--twocol--50-50');
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->removeSectionFromLayoutBuilder('Section 1');

    // Block info.
    $block_type = 'Featured Highlight';
    $block_type_id = 'utexas_featured_highlight';
    $block_name = 'Layout Builder Styles test';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');

    // The one-column layout defaults to "readable" width.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->addSectionToLayoutBuilder('One column');
    $this->savePageLayout();
    $assert->elementExists('css', '.layout--utexas-onecol.readable');
    $assert->elementNotExists('css', '.layout--utexas-onecol.container-fluid');
    // The page title gets set to "readable" width, too.
    $assert->elementExists('css', '.block-page-title-block.readable');

    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: UPDATE
    // Set the section to "Full width of page".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->selectFieldOptionByOptionText('Full width of page');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: READ
    // A "container-fluid" class is added to the section.
    $assert->elementNotExists('css', '.layout.readable');
    $assert->elementExists('css', '.layout.container-fluid');
    // The page title does not get set to "readable" width.
    $assert->elementNotExists('css', '.block-page-title-block.readable');
    // Verify "Border with background" class is not present.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-field-background');
    // Verify "Border without background" class is not present.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-centered-headline');

    // CRUD: UPDATE
    // Add "Border with background" layout builder style to block.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['layout_builder_style_utexas_borders[utexas_border_with_background]' => ['utexas_border_with_background']];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Border & background classes are added to the section.
    $assert->elementExists('css', '.utexas-field-border.utexas-field-background');

    // CRUD: UPDATE
    // Add "Border without background" layout builder style to block.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['layout_builder_style_utexas_borders[utexas_border_without_background]' => ['utexas_border_without_background']];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Border without background classes are added to the section.
    $assert->elementExists('css', '.utexas-field-border.utexas-centered-headline');

    // CRUD: UPDATE
    // Select "No padding" for section.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $form = $this->waitForForm('layout-builder-configure-section');
    $this->clickInputByLabel($form, 'No padding between columns');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: READ
    // A "utexas-layout-no-padding" class is added to the section.
    $assert->elementExists('xpath', '//div[contains(@class, "layout")][contains(@class, "utexas-layout-no-padding")]');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

}
