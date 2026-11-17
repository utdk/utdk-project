<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\Tests\utexas\Traits\TextFormatsTestTraits\TextFormatsTestTrait;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Utexas Google Search tests.
 */
#[RunTestsInSeparateProcesses]
class GoogleSearchTest extends FunctionalJavascriptTestBase {

  use TextFormatsTestTrait;

  /**
   * Test behavior the custom Utexas Google Search module.
   */
  public function testGoogleSearch() {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('google_cse'), 'The Google CSE module is not installed.');
    $this->drupalGet('<front>');
    $assert->elementTextContains('css', '.region-header-tertiary', 'Search');
    $assert->elementTextContains('css', '.utexas-search-form', 'Search');
    $google_pse_id = \Drupal::state()->get('utexas.google_pse_id') ?? '';
    $this->assertEquals('000942273509853053164:aaajrnruaja', $google_pse_id, 'Google PSE ID default is set');
    // Enter a search with markup to demonstrate input sanitization.
    $page->fillField('google-cse-query', '<script>alert("hi");</script>');
    $this->submitForm([], 'Search');
    // Assert that the string contains HTML entities, not rendered tags.
    $raw_content = $this->getSession()->getPage()->getContent();
    $this->assertStringContainsString('Search for <q>&lt;script&gt;alert("hi");&lt;/script&gt;</q>', $raw_content);

    // Test a more realistic search.
    $this->drupalGet('<front>');
    $page->fillField('google-cse-query', 'Web Content Management Office Hours');
    $this->submitForm([], 'Search');
    $this->assertStringContainsString('/search/google?q=Web+Content+Management+Office+Hours', $this->getUrl(), 'Site search submits to /search/google');
    $this->assertStringNotContainsString('form_build_id', $this->getUrl(), 'Form build information is omitted from GET parameters per formUtexasSearchFormAlter');
    $assert->pageTextContains("Search for Web Content Management Office Hours");
    // The breadcrumb block is suppressed from the search page.
    $assert->elementNotExists('css', '.block-system-breadcrumb-block');

    // Confirm setting is editable by Site Manager.
    $this->drupalLogin($this->initializeSiteManager());
    $this->drupalGet('/admin/config/content/utexas');
    $assert->fieldValueEquals('google_pse_id', '000942273509853053164:aaajrnruaja');
    $page->fillField('google_pse_id', 'user-entered-string');
    $this->submitForm([], 'Save configuration');
    $this->drupalGet('/admin/config/content/utexas');
    $assert->fieldValueEquals('google_pse_id', 'user-entered-string');
    $this->drupalLogout();

    // Confirm our Accessibility tweaks to Google markup work.
    $this->container->get('module_installer')->install(['utexas_google_search_test']);
    $this->drupalGet('/google_cse_markup');
    $el = $assert->waitForElement('css', '.gsc-result-info[role="status"]', 10000);
    $this->assertNotEmpty($el, 'The .gsc-result-info element did not get the status role within 10 seconds.');
    // Role=status is added to the GSC result info.
    $assert->elementAttributeContains('css', '.gsc-result-info', 'role', 'status');
    // The search results titles are rendered in an h3 tag.
    $assert->elementExists('css', 'h3.gs-title');
  }

}
