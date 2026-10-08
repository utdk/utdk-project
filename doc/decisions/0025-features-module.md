# 25. Use of the Features module for managing configuration

Date: 2024-06-06

## Status

Accepted

## Context

- Drupal provides a robust [Configuration API](https://www.drupal.org/docs/drupal-apis/configuration-api/configuration-api-overview) that represents site configuration in YAML files, which can then be synced with the actual configuration in the database. This system was originally designed to be used to manage all site configuration in a monolithic way. As such, on its own, it does not support a use case for managing partial site configuration, as would be useful in managing configuration for a Drupal distribution.
- The [Features](https://www.drupal.org/project/features) module provides middleware for Drupal's Configuration API that facilitates bundling subsets of related configuration, installing and updating that configuration on sites. The same configuration management is possible without the use of Features, but would require more verbose code and more manual selection of related configuration.
- Prior to Drupal 8, the Features module played a broader role in development, but the introduction of the Configuration API largely made Features unnecessary for managing configuration for individual sites. It does, however, continue to have a role for Drupal distributions, or for modules reused on multiple sites, as stated on the project page: "If you are building a distribution you can still use Features to export specific configuration into your profile or modules."

## Decision

- Use the Features module to bundle subsets of configuration in the Drupal Kit itself as well as in modules on custom sites.

## Consequences

- Using Features will make packaging, installation, and updating of configuration will easier for our team to manage compared to the programmatic equivalent.
- Since the Features module has a narrower use case than in the past, its adoption has dropped and its maintainership is minimal. It is possible that at some point in the future the Features module will no longer be maintained, at which point we would need to switch to a different methodology. This would not pose any substantial challenges, since Features is simply a tool for managing and deploying configuration that is ultimately stored in the Drupal site database: the Features module could be uninstalled without posing any risk to the integrity of configuration on sites.
