<?php

namespace Drupal\utexas\Commands;

use Drupal\utexas\Permissions;
use Drush\Commands\DrushCommands;

/**
 * Add predefined permissions to an indicated role.
 */
class UtexasPermissions extends DrushCommands {

  /**
   * Commands for assigning predefined permissions to roles.
   *
   * @command utdk_profile:grant
   * @aliases utdkgrant
   * @options set
   *   Whether or not an extra message should be displayed to the user.
   * @options role
   *   Whether or not an extra message should be displayed to the user.
   *
   * @usage utdk_profile:grant --set=manager --role=utexas_site_manager
   */
  public function grant($options = ['role' => NULL, 'set' => NULL]) {
    $selected_role = $this->getOption($options, 'role');
    if (!$selected_role) {
      $this->output()->writeln('You must provide a role argument (e.g., "--role=utexas_site_manager")');
      return;
    }
    $selected_set = $this->getOption($options, 'set');
    if (!$selected_set) {
      $this->output()->writeln('You must provide a permissions set argument (e.g., "--set=manager" or "--set=editor")');
      return;
    }
    if (!in_array($selected_set, ['manager', 'editor'])) {
      $this->output()->writeln('The --set argument must be either "manager" or "editor"');
      return;
    }
    $allowed_roles = Permissions::getAllowedRoles();
    if (!in_array($selected_role, array_keys($allowed_roles))) {
      $this->output()->writeln('The rolename supplied is not supported. Eligible roles include:');
      foreach (array_keys($allowed_roles) as $role) {
        $this->output()->writeln(' * ' . $role);
      }
      return;
    }
    // All criteria are met. Proceed to assign the permissions!
    if (Permissions::assignPermissions($selected_set, $selected_role)) {
      $this->output()->writeln($selected_set . ' permissions have been added to the ' . $selected_role . ' role.');
    }
  }

  /**
   * Get the value of an option.
   *
   * @param array $options
   *   The options array.
   * @param string $name
   *   The option name.
   * @param mixed $default
   *   The default value of the option.
   *
   * @return mixed|null
   *   The option value, defaulting to NULL.
   */
  protected function getOption(array $options, $name, $default = NULL) {
    return $options[$name] ?? $default;
  }

}
