<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests that installing utexas_scheduled_transitions configures the UI.
 */
#[RunTestsInSeparateProcesses]
class ScheduledTransitionsSettingsFormTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Simulate an existing site: a role that can already administer
    // publishing status and create every current content type, before
    // utexas_scheduled_transitions (and its config/permission wiring) is
    // installed.
    \Drupal::service('module_installer')
      ->install(['utexas_scheduled_transitions']);
    $this->drupalLogin($this->testSiteManagerUser);

    $this->drupalGet('/admin/config/workflow/scheduled-transitions');
    // 'administer scheduled transitions' is granted automatically to any
    // role with 'administer site configuration'; no explicit grant here.
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Installing the module restricts transitions to the latest revision.
   */
  public function testOnlyLatestRevisionEnabled(): void {
    $this->assertSession()
      ->elementExists('css', 'input[name="only_latest_revision"][checked]');
  }

  /**
   * Installing the module allows only publish and archive transitions.
   */
  public function testOnlyPublishAndArchiveAllowed(): void {
    $settings = $this->config('scheduled_transitions.settings');
    $this->assertTrue($settings->get('simplified_ui.restrict_transitions'));

    $expected = [];
    foreach ($settings->get('bundles') as $entry) {
      foreach (['publish', 'archive'] as $transition) {
        $expected[] = "{$entry['entity_type']}:{$entry['bundle']}:$transition";
      }
    }
    $this->assertNotEmpty($expected);
    $this->assertEqualsCanonicalizing(
      $expected,
      $settings->get('simplified_ui.allowed_transitions')
    );

    // The settings form reflects the stored restriction.
    $bundle = $settings->get('bundles')[0]['bundle'];
    $this->assertSession()->elementExists(
      'css',
      "input[name=\"allowed_transitions[node:$bundle:publish]\"][checked]"
    );
    $this->assertSession()->elementNotExists(
      'css',
      "input[name=\"allowed_transitions[node:$bundle:create_new_draft]\"][checked]"
    );
  }

  /**
   * Installing the module selects the platform-cron task runner.
   */
  public function testTaskRunnerSetToPlatformCron(): void {
    $this->assertSession()
      ->elementExists('css', 'input[name="task_runner"][value="_none"][checked]');
  }

  /**
   * Installing the module suppresses the "no roles" notice for every bundle.
   */
  public function testNoRolesNoticeAbsent(): void {
    $this->assertSession()->elementTextNotContains(
      'css',
      '#edit-bundles',
      'no roles are currently granted permissions for this type'
    );
  }

}
