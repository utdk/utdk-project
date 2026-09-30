# 9. Dependency version constraints

Date: 2022-12-05

## Status

Accepted

## Context

Drupal has embraced Composer as its dependency management tool. Composer provides a robust system for controlling which versions of a dependency can be installed during the build. Using a [version range](https://getcomposer.org/doc/articles/versions.md#version-range) can reduce dependency conflicts, but it also means that different sites on the same application release could be running different versions of a dependency. Our team places a high value on each Kit release being idempotent.

## Decision

- Define exact versions for `drupal/core`, Drupal modules, and most other assets required in the installation profile.
- Allow exceptions for some low-level libraries, such as `composer/installers`, where exact version requirements could cause dependency conflicts.

## Consequences

- As articulated by Martin Hujer in [24 Tips for Using Composer Efficiently](https://blog.martinhujer.cz/17-tips-for-using-composer-efficiently/#tip-%233%3A-use-specific-dependencies%27-versions-for-applications), this will prevent campus developers (and Pantheon's Autopilot) from updating a Drupal module, or even Drupal core, to a version that has not been tested for integration with the Kit.
- Campus developers must wait for a new version of the UT Drupal Kit to be released before they can update dependencies. This puts the onus on our team to package critical security releases in a timely manner.
