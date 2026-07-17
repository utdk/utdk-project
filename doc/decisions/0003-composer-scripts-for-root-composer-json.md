# 3. Use ComposerScripts to Modify the Root composer.json

Date: 2026-07-17

## Status

Accepted

## Context

The `utdk-project` repository functions as a Pantheon Integrated Composer upstream. Downstream sites inherit the upstream's `composer.json` and receive updates from it via Pantheon's upstream update mechanism. A consequence of this model is that any direct modification to the root `composer.json` in the upstream risks producing merge conflicts in downstream sites if those sites have independently modified the same lines or sections.

Pantheon's own model upstream, [pantheon-upstreams/drupal-composer-managed](https://github.com/pantheon-upstreams/drupal-composer-managed), addresses this by running a PHP script before `composer update` that programmatically applies changes to the root `composer.json` at update time, rather than committing those changes directly to the file:

```json
"scripts": {
    "pre-update-cmd": [
        "DrupalComposerManaged\\ComposerScripts::preUpdate"
    ]
}
```

The UTDK upstream adopted this pattern in [PR #118](https://github.com/utexas-utdk/utdk-project/pull/118) and has used it successfully in subsequent changes (PRs #175, #184, #186, #209, #220).

## Decision

Modify the root `composer.json` only via `ComposerScripts::applyComposerJsonUpdates()` in `upstream-configuration/scripts/ComposerScripts.php`. Do not edit the root `composer.json` directly for changes that would affect downstream sites.

## Consequences

- Downstream sites are protected from merge conflicts on `composer.json` when they pull upstream updates, because the file is regenerated programmatically at `composer update` time rather than a commit.
- Changes to `composer.json` behavior require writing or modifying PHP code in `ComposerScripts.php` rather than editing JSON directly, which increases implementation effort slightly.
- The intent behind a `composer.json` change is documented in code, making it easier to audit and review than an unexplained JSON modification.
