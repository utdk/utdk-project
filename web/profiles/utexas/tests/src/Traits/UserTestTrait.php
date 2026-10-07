<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits;

use Drupal\user\Entity\Role;
use Drupal\utexas\Permissions;

/**
 * General-purpose methods for interacting with Drupal users.
 */
trait UserTestTrait {

  /**
   * Asserts that a user role has a set of permissions.
   *
   * @param \Drupal\user\RoleInterface|string $role
   *   The user role, or its ID.
   * @param string|string[] $permissions
   *   The permission(s) to check.
   */
  protected function assertPermissions($role, $permissions) {
    if (is_string($role)) {
      $role = Role::load($role);
    }
    $this->assertContainsAll((array) $permissions, $role->getPermissions());
  }

  /**
   * Asserts that a haystack contains a set of needles.
   *
   * @param mixed[] $needles
   *   The needles expected to be in the haystack.
   * @param mixed[] $haystack
   *   The haystack.
   */
  protected function assertContainsAll(array $needles, array $haystack) {
    /** @var \Drupal\Tests\BrowserTestBase $this */
    $diff = array_diff($needles, $haystack);
    $this->assertEmpty($diff);
  }

  /**
   * Create a user with Content Editor specific role.
   *
   * @param array $additional_permissions
   *   Array of additional permissions that are needed. (Optional)
   *
   * @return \Drupal\user\Entity\User|false
   *   The user.
   */
  protected function initializeContentEditor(array $additional_permissions = []) {
    /** @var \Drupal\Tests\BrowserTestBase $this */
    $testUser = $this->drupalCreateUser($additional_permissions);
    $testUser->addRole('utexas_content_editor');
    $testUser->save();
    return $testUser;
  }

  /**
   * Create a user with Site Manager specific role.
   *
   * @param array $additional_permissions
   *   Array of additional permissions that are needed. (Optional)
   *
   * @return \Drupal\user\Entity\User|false
   *   The user.
   */
  protected function initializeSiteManager(array $additional_permissions = []) {
    Permissions::assignPermissions('editor', 'utexas_site_manager');

    // /** @var \Drupal\user\UserInterface $testUser */
    $testUser = $this->drupalCreateUser($additional_permissions);
    $testUser->addRole('utexas_site_manager');
    $testUser->save();
    return $testUser;
  }

  /**
   * Create a generic admin user with common permissions.
   *
   * @param array $extra_permissions
   *   Optionally provide extra permissions for the user.
   *
   * @return \Drupal\user\Entity\User|false
   *   The user.
   */
  protected function initializeAdminUser(array $extra_permissions = []) {
    /** @var \Drupal\Tests\BrowserTestBase $this */
    $standard_permissions = [
      'administer site configuration',
      'use text format restricted_html',
      'view the administration theme',
    ];
    if (!empty($extra_permissions)) {
      $standard_permissions = array_merge($standard_permissions, $extra_permissions);
    }
    $user = $this->drupalCreateUser($standard_permissions);
    return $user;
  }

  /**
   * Create a super admin user with all permissions.
   *
   * @return \Drupal\user\Entity\User|false
   *   The user.
   */
  protected function initializeSuperAdminUser() {
    /** @var \Drupal\user\PermissionHandler $user_permissions */
    $user_permissions = $this->container->get('user.permissions');
    $available_permissions = $user_permissions->getPermissions();
    $user = $this->drupalCreateUser(array_keys($available_permissions));

    return $user;
  }

}
