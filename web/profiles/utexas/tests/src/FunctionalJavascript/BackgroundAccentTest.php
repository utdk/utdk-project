<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies background colors/images can be added to sections.
 */
#[RunTestsInSeparateProcesses]
class BackgroundAccentTest extends FunctionalJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testContentEditorUser);
  }

  /**
   * Initial action for all background tests.
   */
  public function testSectionBackgrounds() {
    $this->backgroundImage();
    $this->backgroundColors();
  }

  /**
   * Test background color configuration.
   */
  public function backgroundImage() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // CRUD: CREATE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, 'Header Menu');
    $this->savePageLayout();

    // CRUD: READ
    // Verify that background-accent class is not added by default.
    $assert->elementNotExists('xpath', '//div[contains(@class, "background-accent")]');

    // CRUD: UPDATE
    // Add a background image to the section.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->clickDetailsBySummaryText('Background image');
    $this->addMediaLibraryImage();
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: READ
    // A "background-accent" class has been added around the section.
    $assert->elementExists('css', '.background-accent .layout');
    // The background image style matches the uploaded image.
    $assert->elementAttributeContains('xpath', '//div[contains(@class, "background-accent")]/div', 'style', 'utexas_image_style_1600w_500h/public/image-test.png');
    // There is no blur effect initially.
    $assert->elementNotExists('css', '.background-blur');
    // White background is added to menu block automatically.
    $menu_nav_background_color = $this->getSession()->evaluateScript('jQuery(".background-accent nav").css("background-color")');
    $this->assertSame("rgb(255, 255, 255)", $menu_nav_background_color);

    // CRUD: UPDATE
    // Add a blur effect to background image.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->clickDetailsBySummaryText('Background image');
    $form = $this->waitForForm('layout-builder-configure-section');
    $this->clickInputByLabel($form, 'Apply blur to image?');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: READ
    // Blur is present.
    $assert->elementExists('css', '.background-blur');

    // TEST CLEANUP //
    // Remove test node.
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * Test background color configuration.
   */
  public function backgroundColors() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // The available hex colors & their corresponding rgb values.
    $color_palette = [
      'f2f1ed' => 'rgb(242, 241, 237)',
      'e6ebed' => 'rgb(230, 235, 237)',
      'c2c5c8' => 'rgb(194, 197, 200)',
      '807e76' => 'rgb(128, 126, 118)',
      '5e686e' => 'rgb(94, 104, 110)',
      '487d39' => 'rgb(72, 125, 57)',
      '9d4700' => 'rgb(157, 71, 0)',
      '138791' => 'rgb(19, 135, 145)',
      '074d6a' => 'rgb(7, 77, 106)',
    ];

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // CRUD: CREATE
    // Expand background colors.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->clickDetailsBySummaryText('Background color');

    // CRUD: READ
    // Verify all colors present.
    foreach (array_keys($color_palette) as $hex) {
      $assert->elementExists('xpath', '//input[@name= "layout_settings[background-color-wrapper][background-color]"][@value="' . $hex . '"]');
    }

    // CRUD: UPDATE
    // Add the "Transparent" background color.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->clickDetailsBySummaryText('Background color');
    $form = $this->waitForForm('layout-builder-configure-section');
    $this->clickInputByLabel($form, 'Transparent');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: READ
    // When "Transparent" is selected, no background color CSS class is added.
    $assert->elementNotExists('xpath', '//div[contains(@class, "background-accent")][not(contains(@class, "utexas-bg-"))]');

    // CRUD: UPDATE
    // Add a "non-transparent" background color.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->clickDetailsBySummaryText('Background color');
    $form = $this->waitForForm('layout-builder-configure-section');
    $this->clickInputByLabel($form, 'Bluebonnet ');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: READ
    // Verify correct CSS class is added.
    $assert->elementExists('xpath', '//div[contains(@class, "background-accent")][contains(@class, "utexas-bg-074d6a")]');

    // CRUD: READ
    // Verify rendering of only one background color from the list.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->clickDetailsBySummaryText('Background color');
    $this->verifyBgColor('5e686e', $color_palette['5e686e'], $assert, $page);

    // CRUD: UPDATE
    // Place Recent Content block on page.
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, 'Recent content');
    $this->savePageLayout();

    // CRUD: READ
    // White background is added automatically when a generic block is placed.
    $basic_block_background_color = $this->getSession()->evaluateScript('jQuery(".background-accent div.block-views-blockcontent-recent-block-1").css("background-color")');
    $this->assertSame("rgb(255, 255, 255)", $basic_block_background_color);

    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * Helper function for iterating over color tests.
   */
  private function verifyBgColor($input_hex, $expected_rgb, $assert, $page) {
    $page->selectFieldOption("layout_settings[background-color-wrapper][background-color]", $input_hex);
    $this->saveSectionConfiguration();
    $this->assertTrue($assert->waitForText('You have unsaved changes'));
    // A "background" class is added to the section. The correct color is found.
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-bg-' . $input_hex));
    $actual_background = $this->getSession()->evaluateScript('jQuery(".background-accent.utexas-bg-' . $input_hex . '").css("background-color")');
    $this->assertSame($expected_rgb, $actual_background);
  }

}
