# 18. Roles provided by default

Date: 2022-12-05

## Status

Accepted

Revised by [43. Default roles include Site Manager](0043-default-roles-include-manager.md)

## Context

Although sites can define the number and type of Drupal roles appropriate for their site, Drupal core provides some conventions: the Anonymous user, Authenticated user, Administrator, and Content Editor are installed by the `Standard` installation profile. We want to respect convention so that site roles are familiar to people who have used Drupal before, but we also want to provide a role schema that is both flexible and fits the most common types of UT Drupal Kit sites.

## Decision

- Install the `authenticated` and `anonymous` user with Drupal core's permissions for those roles.
- Do not install the `administrator` role.
- Install a `Content Editor` role that starts with permissions that match the common content editing capabilities for a Drupal Kit site.
- Provide a `Site Manager` role as a separate module that is not installed by default.

## Consequences

- Not providing the `administrator` role will prevent zealous site managers from granting permissions that could expose parts of the configuration interface that should not be edited by most site managers. Developers may still use User 1 for superadmin actions.
- Providing a default `Content Editor` role with Kit-specific permissions will allow most sites to start content editing without having to modify permissions.
- Not installing the `Site Manager` role by default gives more flexibility to sites to determine their own role schema. Note: the `Site Manager` role will be added to "UTDK Managed" sites.
