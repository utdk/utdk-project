# 18. Default roles will include the Site Manager role

Date: 2026-06-29

## Status

Accepted

Amends [18. Roles Provided by Default](0018-roles-provided-by-default.md)

## Context

In [18. Roles Provided by Default](0018-roles-provided-by-default.md), we decided to omit the "Site Manager" role from default installations, stating "not installing the Site Manager role by default gives more flexibility to sites to determine their own role schema."

With years of Drupal Kit installations as a reference, we have found in practice that without providing a default Site Manager role, sites built by other developers end up having unconventional roles and permissions. Some sites omit a managerial role altogether and grant users unlimited "admin" access, which is not something we want to encourage. Other sites glom managerial roles into the Content Editor role, which makes things confusing when we are contacted with support requests related to access.

## Decision

- Install the Site Manager role, populating it with permissions that match our already established set of configuration capabilities for a Drupal Kit site.

## Consequences

- Installing the Site Manager role by default creates more consistency between Drupal Kit sites across service offerings.
- Some sites that do not need the Site Manager role will be installed with an extraneous role; this can be deleted, if desired, after installation.
