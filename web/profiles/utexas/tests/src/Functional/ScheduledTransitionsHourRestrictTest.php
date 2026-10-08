<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use Drupal\node\Entity\Node;
use Drupal\scheduled_transitions\Entity\ScheduledTransition;
use Drupal\scheduled_transitions\ScheduledTransitionsPermissions as Permissions;

/**
 * Tests the top-of-the-hour restriction on scheduled transition forms.
 *
 * @group utexas
 * @group scheduled_transitions
 */
class ScheduledTransitionsHourRestrictTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['utexas_scheduled_transitions'];

  /**
   * A node on standard_workflow, shared across test methods.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // utexas_scheduled_transitions_install() already registers 'page' into
    // 'bundles'. It does not set 'mirror_operations', though, and without a
    // mirror the dedicated permission alone never grants access — see
    // ScheduledTransitionsEntityHooks::entityAccess(). Real gap in the
    // module, not just test setup.
    \Drupal::configFactory()->getEditable('scheduled_transitions.settings')
      ->set('mirror_operations', [
        'view scheduled transition' => 'update',
        'add scheduled transition' => 'update',
        'reschedule scheduled transitions' => 'update',
      ])
      ->save(TRUE);

    $this->node = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'moderation_state' => 'draft',
    ]);
    $this->node->save();

    $user = $this->drupalCreateUser([
      'access content',
      'edit any page content',
      Permissions::viewScheduledTransitionsPermission('node', 'page'),
      Permissions::addScheduledTransitionsPermission('node', 'page'),
      Permissions::rescheduleScheduledTransitionsPermission('node', 'page'),
      'use standard_workflow transition publish',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Submitting the add form with a non-hour time is rejected.
   */
  public function testAddFormRejectsNonHourTime(): void {
    $this->drupalGet($this->node->toUrl('scheduled_transition_add'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Scheduled transitions are only allowed at the top of the hour.');

    $date = new \DateTime('+1 day');
    $this->submitForm([
      'revision' => 'latest_revision',
      'transition' => 'publish',
      // 'on' posts as two sub-fields, combined during validation.
      'on[date]' => $date->format('Y-m-d'),
      'on[time]' => '14:15:00',
    ], 'Schedule transition');

    $this->assertSession()->pageTextContains('Scheduled transitions must occur at the top of the hour');
    $this->assertCount(0, \Drupal::entityTypeManager()->getStorage('scheduled_transition')->loadMultiple());
  }

  /**
   * Submitting the add form with a top-of-hour time succeeds.
   */
  public function testAddFormAcceptsTopOfHourTime(): void {
    $this->drupalGet($this->node->toUrl('scheduled_transition_add'));

    $date = new \DateTime('+1 day');
    $this->submitForm([
      'revision' => 'latest_revision',
      'transition' => 'publish',
      'on[date]' => $date->format('Y-m-d'),
      'on[time]' => '15:00:00',
    ], 'Schedule transition');

    $this->assertSession()->pageTextContains('Scheduled a transition for');
    $transitions = \Drupal::entityTypeManager()->getStorage('scheduled_transition')->loadMultiple();
    $this->assertCount(1, $transitions);
    /** @var \Drupal\scheduled_transitions\Entity\ScheduledTransitionInterface $transition */
    $transition = reset($transitions);
    $this->assertSame('00', (new \DateTime('@' . $transition->getTransitionTime()))->format('i'));
  }

  /**
   * Rescheduling an existing transition to a non-hour time is rejected.
   */
  public function testRescheduleFormRejectsNonHourTime(): void {
    $scheduledTransition = ScheduledTransition::create([
      'entity' => [$this->node],
      'entity_revision_id' => $this->node->getRevisionId(),
      'author' => [$this->loggedInUser->id()],
      'workflow' => 'standard_workflow',
      'moderation_state' => 'published',
      'transition_on' => (new \DateTime('+1 day 16:00'))->getTimestamp(),
    ]);
    $scheduledTransition->save();

    // reschedule-form is unreachable via the dedicated permission alone: a
    // contrib bug in ScheduledTransitionsAccessControlHandler defers to an
    // unrecognized 'reschedule' operation for non-admins. Granting the
    // admin permission works around it here; see patches/.
    $rescheduleUser = $this->drupalCreateUser([
      'access content',
      'edit any page content',
      'view all scheduled transitions',
      Permissions::rescheduleScheduledTransitionsPermission('node', 'page'),
      'use standard_workflow transition publish',
    ]);
    $this->drupalLogin($rescheduleUser);

    $this->drupalGet($scheduledTransition->toUrl('reschedule-form'));
    $this->assertSession()->statusCodeEquals(200);

    $date = new \DateTime('+2 days');
    $this->submitForm([
      'date[date]' => $date->format('Y-m-d'),
      'date[time]' => '09:30:00',
    ], 'Reschedule transition');

    $this->assertSession()->pageTextContains('Scheduled transitions must occur at the top of the hour');
  }

}
