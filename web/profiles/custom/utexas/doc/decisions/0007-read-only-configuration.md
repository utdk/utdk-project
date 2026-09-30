# 7. Read-only configuration

Date: 2022-12-05

## Status

Accepted

Supplements [6. "Managed" configuration vs. "Default" configuration](0006-managed-configuration-vs-default-configuration.md).

## Context

In [6. "Managed" configuration vs. "Default" configuration](0006-managed-configuration-vs-default-configuration.md), we defined the configuration that is completely controlled by our team as "Managed configuration." However, users with administrative-level permissions (and developers who bypass permissions) can still change these elements in the UI. Nothing in the UI indicates that this type of configuration should not be changed.

## Decision

- Programmatically enforce read-only status on configuration pages in the UI for "Managed configuration."
- Add visual warnings explaining this.
- Bundle this functionality in modules which can be uninstalled so that our team, and developers who have forked components, can make modifications.
- Set configuration for `field_storage` that our team manages to `locked: true`.

## Consequences

- Campus developers won't need to consult an external document explaining what configuration they can and can't change.
- Locking `field_storage` adds more development work and potential for regression for our team when trying to change configuration, since we have to unlock the configuration and that change could be inadvertently saved.
