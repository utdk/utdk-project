<?php

namespace Drupal\Tests\utnews_readonly\Functional;

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
    'utnews',
    'utnews_content_type_news',
    'utnews_block_type_news_listing',
    'utnews_view_listing_page',
    'utnews_vocabulary_categories',
    'utnews_vocabulary_authors',
    'utnews_vocabulary_tags',
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
      '/admin/structure/types/manage/utnews_news/fields/add-field',
      '/admin/structure/types/manage/utnews_news/form-display/add-group',
      '/admin/structure/types/manage/utnews_news/display/add-group',
      '/admin/structure/block-content/manage/utnews_article_listing/fields/add-field',
      '/admin/structure/block-content/manage/utnews_article_listing/form-display/add-group',
      '/admin/structure/block-content/manage/utnews_article_listing/display/add-group',
      '/admin/structure/taxonomy/manage/utnews_categories/overview/fields/add-field',
      '/admin/structure/taxonomy/manage/utnews_categories/overview/form-display/add-group',
      '/admin/structure/taxonomy/manage/utnews_categories/overview/display/add-group',
      '/admin/structure/taxonomy/manage/utnews_authors/overview/fields/add-field',
      '/admin/structure/taxonomy/manage/utnews_authors/overview/form-display/add-group',
      '/admin/structure/taxonomy/manage/utnews_authors/overview/display/add-group',
      '/admin/structure/taxonomy/manage/utnews_tags/overview/fields/add-field',
      '/admin/structure/taxonomy/manage/utnews_tags/overview/form-display/add-group',
      '/admin/structure/taxonomy/manage/utnews_tags/overview/display/add-group',
      '/admin/structure/taxonomy/manage/utnews_categories/delete',
      '/admin/structure/taxonomy/manage/utnews_tags/delete',
      '/admin/structure/views/view/utnews_listing_page/delete',
      '/admin/structure/views/view/utnews_listing_block/delete',
    ];
    foreach ($fourohthree as $path) {
      $this->isNotAccessible($path);
    }

    // Pages that should be read-only.
    $read_only = [
      '/admin/structure/types/manage/utnews_news/fields',
      '/admin/structure/types/manage/utnews_news/form-display',
      '/admin/structure/types/manage/utnews_news/display',
      '/admin/structure/block-content/manage/utnews_article_listing/fields',
      '/admin/structure/block-content/manage/utnews_article_listing/form-display',
      '/admin/structure/block-content/manage/utnews_article_listing/display',
      '/admin/structure/taxonomy/manage/utnews_categories/overview/fields',
      '/admin/structure/taxonomy/manage/utnews_categories/overview/form-display',
      '/admin/structure/taxonomy/manage/utnews_categories/overview/display',
      '/admin/structure/taxonomy/manage/utnews_tags',
      '/admin/structure/taxonomy/manage/utnews_tags/overview/fields',
      '/admin/structure/taxonomy/manage/utnews_tags/overview/form-display',
      '/admin/structure/taxonomy/manage/utnews_tags/overview/display',
      '/admin/structure/views/view/utnews_listing_page',
      '/admin/structure/views/view/utnews_listing_block',
      '/admin/structure/taxonomy/manage/utnews_authors/overview',
      '/admin/structure/taxonomy/manage/utnews_tags/overview',
      '/admin/structure/taxonomy/manage/utnews_categories/overview',
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
    $this->assertSession()->pageTextNotContains('The News feature is read-only');
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
    $this->assertSession()->pageTextContains('The News feature is read-only');
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
    $this->assertSession()->pageTextContains('The News feature is read-only');
  }

}
