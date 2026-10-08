<?php

namespace Drupal\Tests\utprof\Traits;

use Drupal\user\Entity\Role;

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
   * Asserts that the current user can access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertAllowed($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Asserts that the current user cannot access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertForbidden($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Create a user with Profile Editor specific role.
   */
  protected function initializeProfileEditor() {
    $this->testUser = $this->drupalCreateUser();
    $testUser = array_values(\Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => $this->testUser->getAccountName()]))[0] ?? FALSE;
    // $testUser->addRole('utexas_profile_editor');
    $testUser->save();
  }

  /**
   * Create a user with Site Manager specific role.
   */
  protected function initializeSiteManager() {
    return $this->drupalCreateUser()->grantPermissions('utexas_site_manager');
  }

  /**
   * Create a generic admin user.
   */
  protected function initializeAdminUser() {
    $isAdmin = TRUE;
    return $this->drupalCreateUser([], NULL, $isAdmin);
  }

}
