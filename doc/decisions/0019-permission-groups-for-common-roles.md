# 19. Permission groups for common roles

Date: 2022-12-05

## Status

Accepted

## Context

It is a truth universally acknowledged that different sites will have different types of authenticated users requiring different permissions. We also acknowledge that Drupal permissions are complicated and that our team has a role in helping site managers assign the correct permissions to their authenticated users. In Drupal Kit 2, we tried to accommodate this by providing granular roles with predefined permissions sets: the `Standard Page Editor` could perform content editing for the `Standard Page` content type, and so on. People who had to manage users on their sites generally found this approach confusing. We need a method for assigning common permission sets that is both easy to use and flexible.

## Decision

Provide a user interface and a command-line tool to allow authorized users to assign the most common editorial and managerial permission sets to any role. For the "UTDK Managed" [service offering](https://ut.service-now.com/sp?id=ut_bs_service_detail&sys_id=d6d65c7c4ff9d200f6897bcd0210c786), where users cannot manage permissions, assign the editorial permission set to the "Content Editor" role and the managerial permission set to the "Site Manager" role.

## Consequences

- Sites on the "UTDK Custom" and "UTDK Self-managed" [service offerings](https://ut.service-now.com/sp?id=ut_bs_service_detail&sys_id=d6d65c7c4ff9d200f6897bcd0210c786) can create as many or as few roles as is appropriate for their needs, and can easily apply common permissions without having to worry whether they are granting too much or too little permission for common actions.
- "UTDK Managed" sites can have a common role schema with consistent permissions across all sites.
- Since users on "UTDK Managed" sites are not allowed to modify permissions (they can only assign roles to users), the managerial permission set, which provides the basis for the "Site Manager" role, cannot include the permission to manage permissions. As a consequence, "UTDK Custom" and "UTDK Self-managed" sites will require a super-user to be able to manage permissions.
