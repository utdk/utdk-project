<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

/**
 * Tests that installing utexas_scheduled_transitions configures the UI.
 *
 * @group utexas
 * @group scheduled_transitions
 */
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
    $bundles = array_keys(
      \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple()
    );
    $permissions = ['administer node published status'];
    foreach ($bundles as $bundle) {
      $permissions[] = "create $bundle content";
    }
    $user = $this->drupalCreateUser($permissions);

    \Drupal::service('module_installer')
      ->install(['utexas_scheduled_transitions']);

    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/workflow/scheduled-transitions');
    // 'administer scheduled transitions' is granted automatically to any
    // role with 'administer node published status'; no explicit grant here.
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
