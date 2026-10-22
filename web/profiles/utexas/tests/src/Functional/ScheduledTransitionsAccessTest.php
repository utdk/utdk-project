<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use Drupal\node\Entity\Node;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests that the Scheduled Transitions UI is gated by permission.
 */
#[RunTestsInSeparateProcesses]
class ScheduledTransitionsAccessTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['scheduled_transitions'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');

    \Drupal::service('module_installer')
      ->install(['utexas_scheduled_transitions']);

  }

  /**
   * A user without any scheduling permission sees and can access nothing.
   */
  public function testAccessDeniedWithoutPermission(): void {
    $node = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'moderation_state' => 'published',
    ]);
    $node->save();

    $user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($user);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    // Only base authenticated permissions: core hides the tabs block when
    // View would be the only tab.
    $this->assertSession()->elementNotExists('css', '.block-local-tasks-block');

    $this->assertForbidden($node->toUrl('scheduled_transitions'));
    $this->assertForbidden($node->toUrl('scheduled_transition_add'));
  }

  /**
   * A user with the bundle-specific permissions sees and can use the UI.
   */
  public function testAccessGrantedWithPermission(): void {
    $node = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'moderation_state' => 'published',
    ]);
    $node->save();

    $this->drupalLogin($this->testContentEditorUser);

    // Check route access first to isolate route vs. tab-rendering issues.
    $this->assertAllowed($node->toUrl('scheduled_transitions'));
    $this->assertAllowed($node->toUrl('scheduled_transition_add'));

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementTextContains('css', '.block-local-tasks-block', 'Scheduled transitions');
  }

  /**
   * The sitewide scheduled transitions report requires its own permission.
   */
  public function testAdminCollectionRequiresViewAllPermission(): void {
    $this->drupalLogin($this->testContentEditorUser);
    $this->assertForbidden('/admin/content/scheduled-transitions');

    $this->drupalLogin($this->testSiteManagerUser);
    $this->assertAllowed('/admin/content/scheduled-transitions');
  }

}
