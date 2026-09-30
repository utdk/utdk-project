<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies that Layout Builder behaves as configured.
 */
trait LayoutBuilderIntegrationTestTrait {

  /**
   * Menu Blocks are not duplicated in the settings tray.
   */
  public function verifyLayoutBuilderIntegrationDuplicateMenuBlocks() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->clickLink('Add block');
    $this->assertTrue($assert->waitForText('Create content block'));

    // Find all <a> links with text "Header Menu" in the settings tray.
    $headerMenuLink = $page->findAll('xpath', '//*[@id="drupal-off-canvas"]//a[text()="Header Menu"]');

    // There is only one link with text "Header Menu".
    $this->assertEquals(1, count($headerMenuLink));

    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

}
