<?php

namespace Drupal\utprof\Command;

use Drupal\utevent\Permissions;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Assigns predefined permissions to specified role.
 */
#[AsCommand(
  name: 'utprof:grant',
  description: 'Assigns predefined permissions to specified role.'
)]
class GrantPermissions {

  /**
   * Add predefined permissions to an indicated role.
   */
  public function __invoke(OutputInterface $output, #[Argument] string $set, #[Argument] string $role): int {
    if (!in_array($set, ['manager', 'editor'])) {
      $output->writeln('The first argument must be either "manager" or "editor". Example command syntax:');
      $output->writeln('dr utprof:grant manager utexas_site_manager');
      return Command::FAILURE;
    }
    $allowed_roles = Permissions::getAllowedRoles();
    if (!in_array($role, array_keys($allowed_roles))) {
      $output->writeln('The rolename supplied is not supported. Eligible roles include:');
      foreach (array_keys($allowed_roles) as $r) {
        $output->writeln(' * ' . $r);
      }
      $output->writeln('Example command syntax: dr utprof:grant manager utexas_site_manager');
      return Command::FAILURE;
    }
    // All criteria are met. Proceed to assign the permissions!
    if (Permissions::assignPermissions($set, $role)) {
      $output->writeln('Profile ' . $set . ' permissions have been added to the ' . $role . ' role.');
    }
    return Command::SUCCESS;
  }

}
