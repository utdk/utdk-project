# 44. Revert to exact version constraints for Drupal core

Date: 2026-07-17

## Status

Accepted

Supersedes [42. Allow floating patch-level version constraints for Drupal core](0042-floating-drupal-core-patch-level-version-constraints.md)

## Context

In ADR 0042, we decided to allow floating patch-level version constraints for `drupal/core-recommended` and `drupal/core-composer-scaffold` (e.g., `~11.3.0`). This change was included in the `3.30.0` release.

Shortly after, we encountered an unexpected consequence: the floating version constraint, combined with `minimum-stability:dev` configuration, allowed Composer to resolve a development version of Drupal core, causing sites to update to a dev release unintentionally (see [issue #3236](https://github.com/utexas-utdk/utdk_profile/issues/3236)).

Upon reflection, we identified additional costs of the floating constraint approach:

- Enforcing only stable releases adds cognitive complexity to our Composer configuration.
- The floating constraint complicates communication with end users. Previously, every Drupal core security release was accompanied by a Drupal Kit hotfix release with a clear announcement. With a floating constraint, core updates happen "invisibly" or require a different, less familiar communication process.
- Developers on the same team may be testing the same change against different Drupal core patch versions depending on when they run `composer install`, complicating reproduction and debugging.

The original motivation for ADR 0042 — reducing the effort of packaging Drupal core updates — can be addressed in other ways, such as a GitHub Action that automates the preparation and staging of hotfix releases.

## Decision

- Require `drupal/core-recommended` and `drupal/core-composer-scaffold` using exact version constraints (e.g., `11.3.10` instead of `~11.3.10`).
- Update the Drupal core version constraint as part of regular Drupal Kit releases. Create a hotfix release for any security release of Drupal core or a contributed module in use.
- Pursue automation (e.g., a GitHub Action) to reduce the manual effort of packaging Drupal core updates, rather than relaxing version constraints.

## Consequences

- Sites on the same Drupal Kit release will run the same Drupal core version, restoring the idempotency established in ADR 0009.
- Our team retains the ability to test and communicate each Drupal core release before it reaches sites.
- The effort of packaging Drupal core releases remains, but is expected to decrease once automation is in place.
