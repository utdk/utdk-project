<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies Social Links field schema & validation.
 */
#[RunTestsInSeparateProcesses]
class SocialLinksTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();

    $this->copySocialLinksIconFiles();
  }

  /**
   * Control method for all Social Links tests.
   */
  public function testSocialLinks() {
    $this->verifySocialLinkIcons();
  }

  /**
   * Test social link icons permissions.
   */
  public function verifySocialLinkIcons() {
    // CRUD: READ.
    $this->drupalLogin($this->testContentEditorUser);
    // A user WITHOUT the 'administer social links data config' cannot access
    // adminstration pages.
    $this->assertForbidden('admin/structure/social-links');
    $this->assertForbidden('admin/structure/social-links/add');
    $this->assertForbidden('admin/structure/social-links/facebook/edit');
    // $this->assertForbidden('admin/structure/social-links/facebook/delete');
    // CRUD: READ.
    $added_permissions = ['administer social links data config'];
    $this->drupalLogin($this->initializeContentEditor($added_permissions));
    // A user WITH the 'administer social links data config' can access
    // adminstration pages.
    $this->assertAllowed('admin/structure/social-links');
    $this->assertAllowed('admin/structure/social-links/add');
    $this->assertAllowed('admin/structure/social-links/facebook/edit');
    // $this->assertAllowed('admin/structure/social-links/facebook/delete');
  }

}
