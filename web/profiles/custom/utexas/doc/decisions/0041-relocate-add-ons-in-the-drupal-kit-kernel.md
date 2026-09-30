# 41. Relocate add-ons in the Drupal Kit kernel
Date: 2026-04-15

## Status

Accepted

## Context

- The UT Drupal Kit provides three add-ons, `utevent`, `utnews`, and `utprof` which can be added as Composer dependencies to individual sites.
- The original motivation for keeping these add-ons outside the kernel was primarily flexibility: not all sites would install the add-ons, and sites that needed extensive modifications could fork the add-ons (though to be clear, forking does not require the originals to be outside of the kernel).
- In practice, most sites do include the add-ons, so there is less "demand" for a leaner codebase that omits the add-ons.
- In practice, few sites have forked the add-ons. Most sites that customized them use Drupal's Configuration Override API.
- Maintaining the add-ons outside the kernel has also introduced more effort during team development, requiring "combo" pull requests across multiple repositories.

## Decision

We will locate the `utevent`, `utnews`, and `utprof` add-ons in the Drupal Kit kernel and subsequently refer to them as (lowercase) "features."

We make this change for the following reasons:

- It facilitates LLM-based coding by providing direct access to more codebase context in a single repository.
- It allows automated tests for News, Events, and Profile to run on pull requests to the kernel, increasing test coverage and helping demonstrate that kernel changes do not negatively affect the features.
- It simplifies development by keeping module code and related Speedway theme assets in the same repository, eliminating the need for combo pull requests across repositories.
- It normalizes Drupal Kit sites by ensuring that all sites have the features present in code, regardless of service offering.
- It reduces Composer dependency-resolution work by removing separate dependencies for these features, which should improve build speed.

## Consequences

- Sites that do not want to use the features will that code present in the codebase but will leave the modules uninstalled.
- Sites that rely on Configuration Override API customizations for the features can continue doing so with no change.
- We will perform due diligence to establish that there is no compatibility risk on UTDK Self-Managed sites that improperly modified the features.
