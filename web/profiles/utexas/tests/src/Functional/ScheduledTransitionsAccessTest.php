<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use Drupal\node\Entity\Node;
use Drupal\scheduled_transitions\Form\ScheduledTransitionsSettingsForm;
use Drupal\scheduled_transitions\ScheduledTransitionsPermissions as Permissions;

/**
 * Tests that the Scheduled Transitions UI is gated by permission.
 *
 * @group utexas
 * @group scheduled_transitions
 */
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

    // 'page' is on standard_workflow by default; also enable it for
    // scheduled transitions. Without 'mirror_operations', the dedicated
    // permission alone never grants access — see
    // ScheduledTransitionsEntityHooks::entityAccess().
    \Drupal::configFactory()->getEditable('scheduled_transitions.settings')
      ->set('bundles', [
        ['entity_type' => 'node', 'bundle' => 'page'],
      ])
      ->set('mirror_operations', [
        'view scheduled transition' => 'update',
        'add scheduled transition' => 'update',
        'reschedule scheduled transitions' => 'update',
      ])
      ->save(TRUE);

    // getBundles() caches its result permanently; parent::setUp() already
    // creates users (computing an empty cache) before this config is saved,
    // so force a refresh.
    \Drupal::service('cache_tags.invalidator')->invalidateTags([ScheduledTransitionsSettingsForm::SETTINGS_TAG]);
    \Drupal::cache()->delete('scheduled_transitions_enabled_bundles');
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
    // Only 'access content': core hides the tabs block when View would be
    // the only tab.
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

    $user = $this->drupalCreateUser([
      'access content',
      'edit any page content',
      // Content Moderation blocks 'update' without a valid transition
      // permission; scheduled_transitions mirrors onto 'update'.
      'use standard_workflow transition create_new_draft',
      Permissions::viewScheduledTransitionsPermission('node', 'page'),
      Permissions::addScheduledTransitionsPermission('node', 'page'),
    ]);
    $this->drupalLogin($user);

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
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($user);
    $this->assertForbidden('/admin/content/scheduled-transitions');

    $user = $this->drupalCreateUser(['view all scheduled transitions']);
    $this->drupalLogin($user);
    $this->assertAllowed('/admin/content/scheduled-transitions');
  }

}
