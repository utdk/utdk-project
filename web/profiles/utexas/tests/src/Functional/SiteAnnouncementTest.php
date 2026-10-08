<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies Social Links field schema & validation.
 */
#[RunTestsInSeparateProcesses]
class SiteAnnouncementTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->copySiteAnnouncementIconFiles();
  }

  /**
   * Control method for all Site Announcement tests.
   */
  public function testSiteAnnouncement() {
    $this->verifyIcons();
    $this->verifyColorSchemes();
    $this->verifySiteAnnouncement();
  }

  /**
   * Test Icons admin pages permissions.
   */
  public function verifyIcons() {
    // CRUD: READ.
    $this->drupalLogin($this->testSiteManagerUser);
    // A user WITHOUT the 'administer utexas announcement icons' cannot access
    // icons adminstration pages.
    $this->assertForbidden('/admin/config/site-announcement/icons');
    $this->assertForbidden('/admin/config/site-announcement/icons/add');
    $this->assertForbidden('/admin/config/site-announcement/icons/beacon/edit');
    $this->assertForbidden('/admin/config/site-announcement/icons/beacon/delete');

    // CRUD: READ.
    $added_permissions = ['administer utexas announcement icons'];
    $this->drupalLogin($this->initializeSiteManager($added_permissions));
    // A user WITH the 'administer utexas announcement icons' can access icons
    // adminstration pages.
    $this->assertAllowed('/admin/config/site-announcement/icons');
    $this->assertAllowed('/admin/config/site-announcement/icons/add');
    $this->assertAllowed('/admin/config/site-announcement/icons/beacon/edit');
    $this->assertAllowed('/admin/config/site-announcement/icons/beacon/delete');

  }

  /**
   * Test Color Scheme admin pages permissions.
   */
  public function verifyColorSchemes() {
    // CRUD: READ.
    $this->drupalLogin($this->testSiteManagerUser);
    // A user WITHOUT the 'administer utexas announcement color schemes' cannot
    // access icons adminstration pages.
    $this->assertForbidden('/admin/config/site-announcement/color-scheme');
    $this->assertForbidden('/admin/config/site-announcement/color-scheme/add');
    $this->assertForbidden('/admin/config/site-announcement/color-scheme/yellow_black/edit');
    $this->assertForbidden('/admin/config/site-announcement/color-scheme/yellow_black/delete');

    // CRUD: READ.
    $added_permissions = ['administer utexas announcement color schemes'];
    $this->drupalLogin($this->initializeSiteManager($added_permissions));
    // A user WITH the 'administer utexas announcement color schemes' can access
    // icons adminstration pages.
    $this->assertAllowed('/admin/config/site-announcement/color-scheme');
    $this->assertAllowed('/admin/config/site-announcement/color-scheme/add');
    $this->assertAllowed('/admin/config/site-announcement/color-scheme/yellow_black/edit');
    $this->assertAllowed('/admin/config/site-announcement/color-scheme/yellow_black/delete');
  }

  /**
   * Test Site Announcement admin page permissions.
   */
  public function verifySiteAnnouncement() {
    // CRUD: READ.
    $this->drupalLogin($this->testContentEditorUser);
    // A user WITHOUT the 'manage utexas site announcement' cannot access
    // adminstration page.
    $this->assertForbidden('/admin/config/site-announcement');

    // CRUD: READ.
    $added_permissions = ['manage utexas site announcement'];
    $this->drupalLogin($this->initializeSiteManager($added_permissions));
    // A user WITH the 'manage utexas site announcement' can access
    // adminstration page.
    $this->assertAllowed('/admin/config/site-announcement');
  }

}
