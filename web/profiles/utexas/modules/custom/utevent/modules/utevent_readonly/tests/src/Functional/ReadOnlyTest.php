<?php

namespace Drupal\Tests\utevent_readonly\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies add-on specific Field UI pages are read-only.
 */
#[RunTestsInSeparateProcesses]
#[Group('utexas')]
class ReadOnlyTest extends BrowserTestBase {

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * Specify the theme to be used in testing.
   *
   * @var string
   */
  protected $defaultTheme = 'speedway';

  /**
   * Modules to enable.
   *
   * @var array
   *
   * @see Drupal\Tests\BrowserTestBase
   */
  protected static $modules = [
    'utevent',
    'utevent_content_type_event',
    'utevent_block_type_event_listing',
    'utevent_vocabulary_location',
    'utevent_vocabulary_tags',
    'utevent_view_listing_page',
    'views_ui',
    'field_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->strictConfigSchema = NULL;
    parent::setUp();
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();
    $admin_user = $this->drupalCreateUser(array_keys($available_permissions));
    $this->drupalLogin($admin_user);
  }

  /**
   * Test which pages admin users have access to.
   */
  public function testReadOnlyPages() {
    // Pages that an admin user *should* have access to.
    $twohundred = [
      '/admin/structure/types/manage/page/fields',
      '/admin/structure/types/manage/page/form-display',
      '/admin/structure/types/manage/page/display',
      '/admin/structure/types/manage/page/fields/add-field',
      '/admin/structure/block-content/manage/basic/fields',
      '/admin/structure/block-content/manage/basic/form-display',
      '/admin/structure/block-content/manage/basic/display',
      '/admin/structure/views/view/content',
      '/admin/structure/views/view/content/delete',
    ];
    foreach ($twohundred as $path) {
      $this->isAccessible($path);
    }

    // Pages that should be forbidden.
    $fourohthree = [
      '/admin/structure/types/manage/utevent_event/fields/add-field',
      '/admin/structure/block-content/manage/utevent_event_listing/fields/add-field',
      '/admin/structure/taxonomy/manage/utevent_location/overview/fields/add-field',
      '/admin/structure/taxonomy/manage/utevent_tags/overview/fields/add-field',
      '/admin/structure/taxonomy/manage/utevent_location/delete',
      '/admin/structure/taxonomy/manage/utevent_tags/delete',
    ];
    foreach ($fourohthree as $path) {
      $this->isNotAccessible($path);
    }

    // Pages that should be read-only.
    $read_only = [
      '/admin/structure/types/manage/utevent_event/fields',
      '/admin/structure/types/manage/utevent_event/form-display',
      '/admin/structure/block-content/manage/utevent_event_listing/fields',
      '/admin/structure/block-content/manage/utevent_event_listing/form-display',
      '/admin/structure/block-content/manage/utevent_event_listing/display',
      '/admin/structure/taxonomy/manage/utevent_location/overview/fields',
      '/admin/structure/taxonomy/manage/utevent_location/overview/form-display',
      '/admin/structure/taxonomy/manage/utevent_location/overview/display',
      '/admin/structure/taxonomy/manage/utevent_tags',
      '/admin/structure/taxonomy/manage/utevent_tags/overview/fields',
      '/admin/structure/taxonomy/manage/utevent_tags/overview/form-display',
      '/admin/structure/taxonomy/manage/utevent_tags/overview/display',
      '/admin/structure/views/view/utevent_listing_page',
      '/admin/structure/views/view/utevent_listing_block',
      '/admin/structure/taxonomy/manage/utevent_tags/overview',
      '/admin/structure/taxonomy/manage/utevent_location/overview',
    ];
    foreach ($read_only as $path) {
      $this->isReadOnly($path);
    }
  }

  /**
   * Check that a given path can be accessed.
   *
   * @param string $path
   *   A Drupal admin URL.
   */
  private function isAccessible($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('The Event feature is read-only');
  }

  /**
   * Check that a given path can be accessed.
   *
   * @param string $path
   *   A Drupal admin URL.
   */
  private function isNotAccessible($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->pageTextContains('The Event feature is read-only');
  }

  /**
   * Check that a given path can be accessed but is read-only.
   *
   * @param string $path
   *   A Drupal admin URL.
   */
  private function isReadOnly($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('The Event feature is read-only');
  }

}
