# 42. Allow floating patch-level version constraints for Drupal core
Date: 2026-04-22

## Status

Superseded by [44. Revert to exact version constraints for Drupal core](0044-revert-to-exact-drupal-core-version-constraints.md); Refines `0009-dependency-version-constraints`

## Context

- ADR 0009 established a policy for exact dependency versions so that a given UT Drupal Kit release would be as idempotent as possible. In practice, this means our team must create a new Drupal Kit release whenever we want sites to receive a Drupal core patch release.
- We observed that some site maintainers have the expectation that they should be able to immediately update to a recent Drupal core release, rather than waiting for us to package it.
- We have high confidence in Drupal core's release process and semantic versioning practices. We can therefore consider changing our policy to allow for a floating, patch-level version constraint for Drupal core.
- We do not have the same confidence in contributed module releases, so this reasoning does not extend to them.

## Decision

- Require Drupal core packages using a version constraint that allows patch-level updates within the supported minor release cycle. Example: `~11.3.0` instead of `11.3.8`.
- Continue to prefer exact version constraints for contributed Drupal modules and most other dependencies.
- Treat Drupal core minor-version updates as still requiring review and a Drupal Kit release.

## Consequences

- Sites will be eligible for important Drupal core fixes faster, and with less effort on our part, than previously.
- Sites on the same Drupal Kit release may be on different patch-level versions of Drupal core depending on when they are updated.
- Our team will spend less time generating Drupal Kit releases while ceding our "stamp of approval" on each Drupal core patch release.
- A Drupal core patch release could still introduce a regression that our previous release gating process might have caught before broader adoption.
- During a development cycle, different developers on our team may be testing the same change against different Drupal core patch versions, which could complicate reproduction and debugging.
