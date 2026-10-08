<?php

namespace Drupal\utexas_scheduled_transitions;

use Drupal\scheduled_transitions\Form\ScheduledTransitionsSettingsForm;
use Drupal\scheduled_transitions\ScheduledTransitionsPermissions;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Helper methods for UTexas scheduled transitions.
 */
class TransitionsHelper {
  use StringTranslationTrait;

  /**
   * Registers a node bundle with Scheduled Transitions and grants access.
   *
   * @param string $bundle
   *   The node bundle machine name.
   */
  public function registerBundle(string $bundle) {
    $entity_type = 'node';

    $config = \Drupal::configFactory()->getEditable('scheduled_transitions.settings');
    $bundles = $config->get('bundles') ?? [];
    $entry = ['entity_type' => $entity_type, 'bundle' => $bundle];
    if (!in_array($entry, $bundles)) {
      $bundles[] = $entry;
      $config->set('bundles', $bundles)->save();
      // ScheduledTransitionsUtility::getBundles() caches the enabled bundle
      // list under this tag; the settings form invalidates it on save, but a
      // direct config save (as done here) does not, so later dynamic
      // permissions in this request would otherwise be computed from a stale
      // cache and miss the bundle just added.
      \Drupal::service('cache_tags.invalidator')->invalidateTags([ScheduledTransitionsSettingsForm::SETTINGS_TAG]);
    }

    // Generate the new permissions dynamically.
    $permissions = [
      ScheduledTransitionsPermissions::viewScheduledTransitionsPermission($entity_type, $bundle),
      ScheduledTransitionsPermissions::addScheduledTransitionsPermission($entity_type, $bundle),
      ScheduledTransitionsPermissions::rescheduleScheduledTransitionsPermission($entity_type, $bundle),
    ];
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();

    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    /** @var \Drupal\user\Entity\Role $role */
    foreach ($roles as $role) {
      if (!$role->hasPermission("create $bundle content")) {
        continue;
      }
      $granted = FALSE;
      foreach ($permissions as $permission) {
        if (isset($available_permissions[$permission]) && !$role->hasPermission($permission)) {
          $role->grantPermission($permission);
          $granted = TRUE;
        }
      }
      if ($granted) {
        $role->save();
        \Drupal::messenger()->addMessage($this->t('Scheduled transitions permissions set for %role role on %bundle content.', [
          '%role' => $role->label(),
          '%bundle' => $bundle,
        ]));
      }
    }
  }

}
