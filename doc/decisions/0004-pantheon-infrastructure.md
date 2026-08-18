# 4. Pantheon is the only officially supported hosting platform

Date: 2026-08-17

## Status

Accepted

## Context

When the Drupal Kit's first stable release was made available in 2021, we held a platform-agnostic stance toward hosting, only specifying compatibility and minimum requirements for PHP, databases, and web servers. This prevented us from making certain assumptions about sites' configuration, such as whether they used Apache or Nginx, or whether they had edge-caching, but we felt it was necessary for adoption: we felt we couldn't require everyone to use Pantheon simply to get a Drupal Kit site up and running.

Over time, the preponderance of sites have been created or migrated to Pantheon. As of 2026, we know of no production sites hosted elsewhere. Over time, we also implicitly began assuming Pantheon architecture. For example, the `pantheon.yml` and/or `pantheon.upstream.yml` determines the PHP version the site is using and other infrastructure decisions, and the upstream-configuration's `ComposerScripts.php` adds a Composer platform constraint based on the Pantheon-indicated PHP version.

## Decision

Adopt a policy that states Pantheon Web Hosting is the only officially supported platform for the Drupal Kit.

## Consequences

- Having all sites hosted on Pantheon makes it easier for us to audit sites for aberrant or divergent configuration and to provide CDN-level enhancements, such as automatic bot blocking and image optimization.
- This decision does not technically preclude someone from using the Drupal Kit on another platform, such as UT Web; it simply establishes that we do not provide official support for other platforms.
