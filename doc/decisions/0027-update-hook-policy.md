# 27. Preserving past update hooks

Date: 2024-10-10

## Status

Accepted

## Context

- [Update hooks](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Extension%21module.api.php/function/hook_update_N/10) are Drupal's methodology to programmatically introduce configuration or other database changes to an existing site.
- Once an update hook has executed on a given site, the code no longer serves a purpose.
- As of this writing, our update hooks span approximately 1,000 lines of code in `utexas.install`
- We should decide whether or not to periodically remove "old" update hooks that have presumably run on all extant sites, as [Drupal core does ](https://www.drupal.org/node/3442097) during major version releases.

## Decision

- Leave update hooks in perpetuity.

## Consequences

- The code for the update hooks will be a minor developer experience annoyance, requiring scrolling through a file of 1,000+ lines of code to add a new update hook.
- By retaining the update hooks in perpetuity, there is nothing to prevent an out-of-date site from updating safely to the latest version of the Kit, including all database configuration changes.
