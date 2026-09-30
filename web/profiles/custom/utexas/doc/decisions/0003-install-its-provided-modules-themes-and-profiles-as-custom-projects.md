# 3. Install ITS-provided modules, themes, and profiles as custom projects

Date: 2021-03-31

## Status

Accepted

## Context

Drupal modules, themes, and profiles may be installed via [composer-installers](https://github.com/composer/installers) as contributed or custom types (e.g., `drupal-module` or `drupal-custom-module`). We previously installed our Drupal Kit components in `contrib` directories because that seemed to be the only reasonable option for Composer-based installation at the time, and we assumed that the `/custom/` directories would be used for versioned code.

The [model set out by Pantheon](https://github.com/pantheon-upstreams/drupal-project/blob/master/.gitignore#L32-L36) proposes a different convention: the `/custom/` directories should be used for Composer-provided packages:

> When a development team creates one or more custom modules that
> are intended for use on more than one site, the typical strategy
> is to register them in Packagist and give them the type
> `drupal-custom-module` instead of `drupal-module`. This will cause
> Composer to install them to the directory `modules/custom`.

## Decision

Install Drupal Kit components in `/custom/` directories. Site-specific custom code should go in a namespaced directory (e.g., `/modules/<namespace>/<modulename>`).

## Consequences

1. Developers unfamiliar with this paradigm will need to adjust their practice around where to place custom code.
1. Existing UT Drupal Kit 3 sites (pre-integrated Composer) must update their root level `composer.json` to include installer paths for `drupal-custom-module`, `drupal-custom-theme`, and `drupal-custom-profile` before updating.
