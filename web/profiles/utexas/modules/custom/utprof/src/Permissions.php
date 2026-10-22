<?php

namespace Drupal\utprof;

use Drupal\user\Entity\Role;

/**
 * Provided pre-defined permissions for utprof.
 */
class Permissions {

  /**
   * Permissions associated with feature management.
   *
   * @var array
   */
  public static $manager = [
    'administer utprof',
  ];

  /**
   * Permissions associated with content editing.
   *
   * @var array
   */
  public static $editor = [
    'add scheduled transitions node utprof_profile',
    'create terms in utprof_groups',
    'create terms in utprof_tags',
    'create utprof_profile content',
    'delete any utprof_profile content',
    'delete own utprof_profile content',
    'delete terms in utprof_groups',
    'delete terms in utprof_tags',
    'delete utprof_profile revisions',
    'edit any utprof_profile content',
    'edit own utprof_profile content',
    'edit terms in utprof_groups',
    'edit terms in utprof_tags',
    'reschedule scheduled transitions node utprof_profile',
    'revert utprof_profile revisions',
    'view scheduled transitions node utprof_profile',
    'view utprof_profile revisions',
  ];

  /**
   * Manipulate an array provided by this class for use in an HTML table.
   *
   * @param array $array
   *   A permissions array defined in this class.
   *
   * @return array
   *   A simple two-value array.
   */
  public static function displayPermissions(array $array) {
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();
    $rows = [];
    foreach ($array as $name) {
      if (in_array($name, array_keys($available_permissions))) {
        $rows[] = [$available_permissions[$name]['title'], $name];
      }
    }
    return $rows;
  }

  /**
   * Retrieve a subset of roles in the system.
   *
   * @return array
   *   An array of Drupal role objects.
   */
  public static function getAllowedRoles() {
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    // Do not allow assigning these permissions to `anonymous`/`authenticated`.
    unset($roles['anonymous']);
    unset($roles['authenticated']);
    return $roles;
  }

  /**
   * Save a set of permissions to a given role.
   *
   * @param string $set
   *   The internal set (either 'manager' or 'editor')
   * @param string $role
   *   A valid Drupal role machine name.
   *
   * @return bool
   *   Whether or not the save was successful.
   */
  public static function assignPermissions($set, $role) {
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();
    if (!$role = Role::load($role)) {
      return FALSE;
    }
    foreach (self::$$set as $permission) {
      if (in_array($permission, array_keys($available_permissions))) {
        $role->grantPermission($permission);
      }
    }
    return $role->save();
  }

}
