<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\Tests\utexas\Traits\TextFormatsTestTraits\TextFormatsTestTrait;
use Drupal\user\RoleInterface;
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
    $this->assertStringContainsString('/search/google?keys=Web+Content+Management+Office+Hours', $this->getUrl(), 'Site search submits to /search/google');
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

    // Simulate an update from a site with Google CSE.
    // @todo Remove this after all sites have updated to 3.32.0.
    $module_installer = $this->container->get('module_installer');
    $module_installer->uninstall(['utexas_google_search']);
    $module_installer->install(['legacy_google_cse']);
    $config = \Drupal::configFactory()->getEditable('search.settings');
    $config->set('default_page', 'google_cse_search');
    $config->save();
    // Add permissions to anonymous role.
    $anon_perms = [
      'access content',
      'search Google CSE',
      'search content',
    ];
    $entity_type_manager = \Drupal::entityTypeManager();
    /** @var Drupal\user\Entity\Role $anon */
    $anon = $entity_type_manager->getStorage('user_role')->load(RoleInterface::ANONYMOUS_ID);
    foreach ($anon_perms as $perm) {
      $anon->grantPermission($perm);
    }
    $anon->save();
    $this->drupalGet('<front>');
    // Confirm Legacy Google CSE search is present in header tertiary.
    $assert->elementTextContains('css', '.region-header-tertiary', 'Search');
    $assert->elementTextContains('css', '.search-block-form', 'Search');
    $module_installer->install(['utexas_google_search']);
    $this->drupalGet('<front>');
    drupal_flush_all_caches();
    // Confirm that the hook_install() for utexas_google_search...
    // (A) Migrates the Google PSE ID.
    $google_pse_id = \Drupal::state()->get('utexas.google_pse_id') ?? '';
    $this->assertEquals('testonlyvalue', $google_pse_id, 'Google PSE ID migrated to the Drupal state value');
    // (B) Removes the legacy search block
    $assert->elementNotExists('css', '.search-block-form');
    // (c) Replaces it with the new Utexas Google Search block
    $assert->elementTextContains('css', '.region-header-tertiary', 'Search');
    $assert->elementTextContains('css', '.utexas-search-form', 'Search');

    // Confirm our Accessibility tweaks to Google markup work.
    $this->drupalGet('/google_cse_markup');
    $el = $assert->waitForElement('css', '.gsc-result-info[role="status"]', 10000);
    $this->assertNotEmpty($el, 'The .gsc-result-info element did not get the status role within 10 seconds.');
    // Role=status is added to the GSC result info.
    $assert->elementAttributeContains('css', '.gsc-result-info', 'role', 'status');
    // The search results titles are rendered in an h3 tag.
    $assert->elementExists('css', 'h3.gs-title');
  }

}
