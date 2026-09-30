# 4. Provide versionable settings.php file through upstream repository

Date: 2021-04-28

## Status

Accepted

## Context

During a normal site installation, Drupal appends a `hash_salt` setting to `settings.php`. This setting is required to be present for the site to function and should not be public. Drupal convention expects the `settings.php` to be versioned (it is not included in the `.gitignore`). This convention ensures that the hash salt value will be present in all site environments. We initially explored using `core-composer-scaffold` to add this file instead, under the rationale that it allowed us to further obscure the `settings.php` file since it would be VCS-ignored, and it  would allow us to push changes to the `settings.php` to non-Pantheon sites.

## Decision

Provide a default `settings.php` file through the project template (the upstream repository) and direct it to be versioned in the codebase by not including it in the `.gitignore` file, rather than using `core-composer-scaffold` to retrieve the file each time a codebase is built.

## Consequences

- Sites on Pantheon are not affected, since Pantheon provides an environment variable for the hash salt instead of relying on a value in the `settings.php` file
- Local development using Lando is not affected, since Lando mimics the Pantheon environment variable.
- Local development with Docksal, where a hash salt is overwritten upon a subsequent `composer install`, will be avoided.
- Sites hosted elsewhere will reliably have a hash salt in the codebase after installation without additional developer involvement.
- A marginally-sensitive setting will be in version control; developers unaware of Drupal best practices around the `settings.php` file could inadvertently make this public.
