<?php

declare(strict_types=1);

namespace Drupal\Tests\utnews\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Functional test for query parameter caching.
 */
class CacheTest extends BrowserTestBase {

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
    'utnews_view_listing_page',
    'utnews_demo_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->strictConfigSchema = NULL;
    parent::setUp();
  }

  /**
   * Legacy facet-style query parameters are cache-normalized.
   */
  public function testCacheability() {
    $assert = $this->assertSession();
    drupal_flush_all_caches();
    // On a cold cache, the page cache is a MISS.
    $this->drupalGet('/news');
    $assert->responseHeaderEquals('X-Drupal-Cache', 'MISS');

    // A second visit returns a cache HIT.
    $this->drupalGet('/news');
    $assert->responseHeaderEquals('X-Drupal-Cache', 'HIT');

    // A legacy query facets query parameters return cache HIT, using the
    // normalized cache ID of the base request.
    $this->drupalGet('/news', [
      'query' => [
        'f' => ['0' => 'author:8'],
      ],
    ]);
    $assert->responseHeaderEquals('X-Drupal-Cache', 'HIT');

    // Combined exposed filter/facet query parameters are respected.
    $this->drupalGet('/news', [
      'query' => [
        'f' => ['0' => 'author:8'],
        'tags' => '1',
      ],
    ]);
    $assert->responseHeaderEquals('X-Drupal-Cache', 'MISS');
  }

}
