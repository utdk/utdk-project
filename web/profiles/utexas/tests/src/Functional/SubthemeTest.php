<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies subtheme-ing behaves as designed.
 */
#[RunTestsInSeparateProcesses]
class SubthemeTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();
  }

  /**
   * A sub-theme can modify Speedway in the ways we've intended.
   */
  public function testSubtheme() {
    $assert = $this->assertSession();
    \Drupal::service('theme_installer')->install(['speedway_test_subtheme']);
    \Drupal::configFactory()
      ->getEditable('system.theme')
      ->set('default', 'speedway_test_subtheme')->save();
    $this->drupalGet("<front>");
    // Content override is present in the footer...
    $assert->responseContains('We are overriding the footer, but inheriting everything else!');
    // ...but the header is inherited!
    $assert->responseContains('<section class="ut-upper-header">');
    $settings = \Drupal::configFactory()
      ->getEditable('speedway_test_subtheme.settings');
    $settings->set('parent_link_title', '[First Parent](https://firstparent.utexas.edu) | [Second Parent](https://secondparent.utexas.edu)');
    $settings->save();
    $this->drupalGet("<front>");
    $assert->linkExists('First Parent');
    $assert->linkExists('Second Parent');
  }

}
