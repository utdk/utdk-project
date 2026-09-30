# 2. Locate patches on ITS-managed server

Date: 2021-03-18

## Status

Accepted

## Context

Patches applied via `composer-patches` may be local references, but this method is problematic in the context of the `utdk_profile` package-as-requirement: local patches require root-relative paths, and the root location may be different between sites.

Therefore, we must reference patches remotely. Although the patches we typically use are available on drupal.org, the Drupal team's implicit recommendation seems to be "don't rely on our servers at deployment time." Additionally, patchfiles served via Gitlab merge requests have the potential to be dynamic, meaning they could change or disappear. We could commit patchfiles to this repository and reference them from its public mirror, but that makes problematic staging the removal of a patch before all sites depending on that patch have updated to a version that does not need the patch.

## Decision

Locate all patches -- both those found originally on drupal.org and those which we originate -- in an external ITS-managed HTTP resource, specifically at https://drupalkit.its.utexas.edu/patches.

Patches are tracked in https://github.austin.utexas.edu/eis1-wcs/utdk_patches.

## Consequences

1. Patches will never change or disappear without us actively causing that change.
1. All sites will depend on https://drupalkit.its.utexas.edu/patches being accessible during Composer build time.
