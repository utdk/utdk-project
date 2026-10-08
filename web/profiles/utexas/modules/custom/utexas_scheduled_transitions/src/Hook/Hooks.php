<?php

namespace Drupal\utexas_scheduled_transitions\Hook;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\node\NodeTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\scheduled_transitions\Form\ScheduledTransitionsSettingsForm;
use Drupal\utexas_scheduled_transitions\TransitionsHelper;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Sets site defaults for `simplified_ui` and `automation.messenger` settings.
   */
  public function applyScheduledTransitionsSettingsDefaults() {
    $config = \Drupal::configFactory()->getEditable('scheduled_transitions.settings');
    $config
      ->set('simplified_ui.only_latest_revision', TRUE)
      ->set('simplified_ui.restrict_transitions', FALSE)
      ->set('simplified_ui.allowed_transitions', $config->get('simplified_ui.allowed_transitions') ?? [])
      ->set('automation.cron_create_queue_items', FALSE)
      ->set('automation.messenger', FALSE)
      ->save();
    // See the note in registerBundle() about why this direct config save
    // requires an explicit cache tag invalidation.
    \Drupal::service('cache_tags.invalidator')->invalidateTags([ScheduledTransitionsSettingsForm::SETTINGS_TAG]);
  }

  /**
   * Grants administer scheduled transitions permission.
   */
  public function grantAdministerPermission() {
    $indicator_permission = 'administer node published status';
    $permissions = [
      'administer scheduled transitions',
      'view all scheduled transitions',
    ];
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();

    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    /** @var \Drupal\user\Entity\Role $role */
    foreach ($roles as $role) {
      if (!$role->hasPermission($indicator_permission)) {
        continue;
      }
      foreach ($permissions as $permission) {
        if (!isset($available_permissions[$permission]) || $role->hasPermission($permission)) {
          continue;
        }
        $role->grantPermission($permission);
        $role->save();
        \Drupal::messenger()->addMessage($this->t('The %permission permission has been granted to the %role role.', [
          '%role' => $role->label(),
          '%permission' => $permission,
        ]));
      }
    }
  }

  /**
   * Implements hook_node_type_insert().
   */
  #[Hook('node_type_insert')]
  public function nodeTypeInsert(NodeTypeInterface $type) {
    (new TransitionsHelper())->registerBundle($type->id());
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    // The form_id for scheduled transitions forms includes the entity type.
    // This pattern matching catches both add and reschedule variants.
    if (strpos($form_id, 'scheduled_transition') !== FALSE &&
        (strpos($form_id, 'add') !== FALSE || strpos($form_id, 'reschedule') !== FALSE)) {
      // Attach the hour-restrict library for client-side behavior.
      $form['#attached']['library'][] = 'utexas_scheduled_transitions/datetime_hour_restrict';

      $form['#validate'][] = [$this, 'validateTime'];

      $help_text = '<div class="form-help-text">' .
        $this->t('Scheduled transitions are only allowed at the top of the hour.') .
        '</div>';

      if (isset($form['scheduled_transitions']['new_meta']['on'])) {

        $form['scheduled_transitions']['new_meta']['on']['#suffix'] = $help_text;
      }
      elseif (isset($form['date'])) {

        $form['date']['#suffix'] = $help_text;
      }
    }
  }

  /**
   * Custom validation handler for scheduled transitions form.
   */
  public function validateTime(&$form, FormStateInterface $form_state) {
    // The add form posts 'on' and the reschedule form posts 'date', both
    // top-level (the 'scheduled_transitions' wrapper isn't a #tree element).
    // By the time this runs, element validation has converted the value to
    // a DrupalDateTime.
    if (isset($form['scheduled_transitions']['new_meta']['on'])) {
      $date = $form_state->getValue('on');
      $error_element = $form['scheduled_transitions']['new_meta']['on'];
    }
    elseif (isset($form['date'])) {
      $date = $form_state->getValue('date');
      $error_element = $form['date'];
    }
    else {
      return;
    }

    if (!($date instanceof DrupalDateTime) || $date->hasErrors()) {
      return;
    }

    $minutes = (int) $date->format('i');
    $seconds = (int) $date->format('s');

    if ($minutes !== 0 || $seconds !== 0) {
      $form_state->setError(
        $error_element,
        $this->t('Scheduled transitions must occur at the top of the hour (minutes and seconds must be 00:00).')
      );
    }
  }

}
