<?php

namespace Drupal\utexas;

use Drupal\user\Entity\Role;

/**
 * Provided pre-defined permissions for utexas.
 */
class Permissions {

  /**
   * Permissions associated with site management.
   *
   * @var array
   */
  public static $manager = [
    'access site reports',
    'access toolbar',
    'access user profiles',
    'administer addtoany',
    'administer blocks',
    'administer meta tags',
    'administer node last updated date',
    'administer redirects',
    'administer site configuration',
    'administer social links data config',
    'administer themes',
    'administer twitter widget entities',
    'administer url aliases',
    'administer users',
    'assign all roles',
    'bypass node access',
    'cancel account',
    'clone node entity',
    'delete any media',
    'manage utexas site announcement',
    'update any media',
    'use text format full_html',
  ];

  /**
   * Permissions associated with content editing.
   *
   * @var array
   */
  public static $editor = [
    'access administration pages',
    'access block library',
    'access content overview',
    'access contextual links',
    'access entity usage statistics',
    'access files overview',
    'access media overview',
    'access site in maintenance mode',
    'access taxonomy overview',
    'access toolbar',
    'access trash',
    'administer block content',
    'administer breadcrumbs visibility config',
    'administer menu',
    'administer node last updated date',
    'administer node published status',
    'administer page display visibility config',
    'configure any layout',
    'create and edit custom blocks',
    'create article content',
    'create media',
    'create page content',
    'create terms in tags',
    'create url aliases',
    'create utexas_flex_page content',
    'delete all revisions',
    'delete any article content',
    'delete any file',
    'delete any media',
    'delete any page content',
    'delete any utexas_flex_page content',
    'delete article revisions',
    'delete media',
    'delete own article content',
    'delete own page content',
    'delete own utexas_flex_page content',
    'delete page revisions',
    'delete terms in tags',
    'delete utexas_flex_page revisions',
    'edit any article content',
    'edit any page content',
    'edit any utexas_flex_page content',
    'edit own article content',
    'edit own page content',
    'edit own utexas_flex_page content',
    'edit terms in tags',
    'link to any page',
    'purge node entities',
    'restore node entities',
    'revert all revisions',
    'revert article revisions',
    'revert page revisions',
    'revert utexas_flex_page revisions',
    'update any media',
    'update media',
    'use advanced search',
    'use standard_workflow transition archive',
    'use standard_workflow transition create_new_draft',
    'use standard_workflow transition publish',
    'use text format basic_html',
    'use text format flex_html',
    'use text format restricted_html',
    'view all media revisions',
    'view all revisions',
    'view any unpublished content',
    'view latest version',
    'view article revisions',
    'view deleted entities',
    'view own unpublished content',
    'view own unpublished media',
    'view page revisions',
    'view the administration theme',
    'view utexas_flex_page revisions',
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
