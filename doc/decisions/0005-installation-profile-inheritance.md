# 5. Installation profile inheritance

Date: 2022-12-05

## Status

Accepted

## Context

Our team has decided the UTexas installation profile should include elements common to most Drupal sites, such as `Basic` page, `Full HTML`, and the `Anonymous` and `Authenticated` user roles. These elements originate in Drupal's `Standard` installation profile. It is currently not possible for a [Drupal installation profile to define a base/parent profile](https://www.drupal.org/project/drupal/issues/3266057).

We need to develop a method for including these common Drupal elements.

## Decision

Copy verbatim the configuration elements we want to include from `/profiles/standard/config/install` into the UTexas installation profile.

## Consequences

- Content editors coming from other Drupal sites will find familiar elements, with identical names and behaviors.
- If and when Drupal core changes its configuration in the `Standard` installation profile, the UTexas installation profile won't automatically inherit those changes, becoming out of sync with Drupal core.
