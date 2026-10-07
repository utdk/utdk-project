<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\Tests\utexas\Traits\WidgetsTestTraits\FeaturedHighlightTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\FlexContentAreaTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\FlexListTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\FlexPageRevisionsTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\HeroTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\ImageLinkTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\LayoutBuilderIntegrationTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\PhotoContentAreaTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\PromoListTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\PromoUnitTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\QuickLinksTestTrait;
use Drupal\Tests\utexas\Traits\WidgetsTestTraits\ResourcesTestTrait;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies custom compound field schema, validation, & output.
 */
#[RunTestsInSeparateProcesses]
class CustomWidgetsTest extends FunctionalJavascriptTestBase {

  use FeaturedHighlightTestTrait;
  use FlexContentAreaTestTrait;
  use FlexListTestTrait;
  use FlexPageRevisionsTestTrait;
  use HeroTestTrait;
  use ImageLinkTestTrait;
  use LayoutBuilderIntegrationTestTrait;
  use PhotoContentAreaTestTrait;
  use PromoListTestTrait;
  use PromoUnitTestTrait;
  use QuickLinksTestTrait;
  use ResourcesTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->copyTestFiles();
    $this->drupalLogin($this->testSiteManagerUser);
  }

  /**
   * Test any custom widgets sequentially, using the same installation.
   */
  public function testCustomWidgets() {
    $this->verifyFeaturedHighlight();
    $this->verifyFlexContentArea();
    $this->verifyFlexContentAreaMultiple();
    $this->verifyFlexList();
    $this->verifyFlexPageRevisions();
    $this->verifyHero();
    $this->verifyImageLink();
    $this->verifyLayoutBuilderIntegrationDuplicateMenuBlocks();
    $this->verifyPhotoContentArea();
    $this->verifyPromoList();
    $this->verifyPromoListMultiple();
    $this->verifyPromoUnit();
    $this->verifyPromoUnitMultiple();
    $this->verifyQuickLinks();
    $this->verifyResources();
    $this->verifyResourcesMultiple();
  }

}
