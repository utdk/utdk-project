<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verify functionality of 1, 2, 3, and 4 column layouts.
 */
#[RunTestsInSeparateProcesses]
class LayoutsTest extends FunctionalJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testSiteManagerUser);
  }

  /**
   * Initial action for all layout tests.
   */
  public function testLayouts() {
    // CRUD: CREATE
    // Create flex page.
    $flex_page_id = $this->createFlexPage();

    // Test each layout.
    $this->verifyOneColumnLayout($flex_page_id);
    $this->verifyTwoColumnLayout($flex_page_id);
    $this->verifyThreeColumnLayout($flex_page_id);
    $this->verifyFourColumnLayout($flex_page_id);

    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * One column functionality.
   *
   * @param string $flex_page_id
   *   The node ID of the Layout Builder enabled page in question.
   */
  public function verifyOneColumnLayout($flex_page_id) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE
    // Remove default layout section.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->removeSectionFromLayoutBuilder('Section 1');
    $this->savePageLayout();

    // CRUD: CREATE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->addSectionToLayoutBuilder('One column');

    // CRUD: READ
    // Verify that correct class is added.
    $assert->elementExists('css', '.layout--utexas-onecol');
    // Verify Background accent & color options available.
    $this->openSectionConfiguration('Section 1');
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: DELETE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->removeSectionFromLayoutBuilder('Section 1');
    $this->savePageLayout();
  }

  /**
   * Two column functionality.
   *
   * @param string $flex_page_id
   *   The node ID of the Layout Builder enabled page in question.
   */
  public function verifyTwoColumnLayout($flex_page_id) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->addSectionToLayoutBuilder('Two column');

    // CRUD: READ
    // Verify Background accent & color options available.
    $this->openSectionConfiguration('Section 1');
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    $layout_css_suffix = 'twocol';

    // CRUD: READ.
    $this->verifyRatio($flex_page_id, '50%/50%', '50-50', $layout_css_suffix);
    $this->verifyRatio($flex_page_id, '33%/67%', '33-67', $layout_css_suffix);
    $this->verifyRatio($flex_page_id, '67%/33%', '67-33', $layout_css_suffix);
    $this->verifyRatio($flex_page_id, '25%/75%', '25-75', $layout_css_suffix);
    $this->verifyRatio($flex_page_id, '75%/25%', '75-25', $layout_css_suffix);

    // CRUD: DELETE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->removeSectionFromLayoutBuilder('Section 1');
    $this->savePageLayout();
  }

  /**
   * Three column functionality.
   *
   * @param string $flex_page_id
   *   The node ID of the Layout Builder enabled page in question.
   */
  public function verifyThreeColumnLayout($flex_page_id) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->addSectionToLayoutBuilder('Three column');

    // CRUD: READ
    // Verify Background accent & color options available.
    $this->openSectionConfiguration('Section 1');
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    $layout_css_suffix = 'threecol';

    // CRUD: READ.
    $this->verifyRatio($flex_page_id, '25%/50%/25%', '25-50-25', $layout_css_suffix);
    $this->verifyRatio($flex_page_id, '33%/34%/33%', '33-34-33', $layout_css_suffix);
    $this->verifyRatio($flex_page_id, '50%/25%/25%', '50-25-25', $layout_css_suffix);
    $this->verifyRatio($flex_page_id, '25%/25%/50%', '25-25-50', $layout_css_suffix);

    // CRUD: DELETE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->removeSectionFromLayoutBuilder('Section 1');
    $this->savePageLayout();
  }

  /**
   * Four column functionality.
   *
   * @param string $flex_page_id
   *   The node ID of the Layout Builder enabled page in question.
   */
  public function verifyFourColumnLayout($flex_page_id) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->addSectionToLayoutBuilder('Four column');

    // CRUD: READ
    // Verify that correct class is added.
    $assert->elementExists('css', '.utexas-layout--fourcol');
    // Verify Background accent & color options available.
    $this->openSectionConfiguration('Section 1');
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: DELETE.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->removeSectionFromLayoutBuilder('Section 1');
    $this->savePageLayout();
  }

  /**
   * Verify ratio functionality.
   *
   * @param string $flex_page_id
   *   The node ID of the Layout Builder enabled page in question.
   * @param string $ratio_text
   *   The text of the ratio option element.
   * @param string $ratio_css
   *   The CSS suffix for the ratio.
   * @param string $layout_css
   *   The CSS suffix for the layout.
   */
  private function verifyRatio($flex_page_id, $ratio_text, $ratio_css, $layout_css) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE
    // Open layout builder section and verify desired column ratio.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->openSectionConfiguration('Section 1');
    $this->selectFieldOptionByOptionText($ratio_text);
    $this->saveSectionConfiguration();
    $this->savePageLayout();

    // CRUD: READ
    // Generates expected CSS.
    $this->assertNotEmpty(
      $assert->waitForElementVisible(
        'css',
        '.utexas-layout--' . $layout_css . '.utexas-layout--' . $layout_css . '--' . $ratio_css
      )
    );
  }

}
