# 6. "Managed" configuration vs. "Default" configuration

Date: 2022-12-05

## Status

Accepted

Supplemented by [7. Read-only configuration](0007-read-only-configuration.md)

## Context

Drupal 8 introduced a comprehensive system for installing and managing site configuration. Configuration defaults can be stored in `yaml` files whose directives can be written to the site database during installation of a profile or module. After installation, much of this configuration can be updated through the UI. This creates a dilemma: how do we designate which configuration should be managed by our team and what configuration can be changed in sites? Drupal permissions alone can't regulate this: some administrative permissions are broad. For example, we want to allow sites to add new Layout Builder Styles but we don't want them to change the ones we provide.

We need a methodology for handling which configuration can be changed on a per site basis and which is under the control of the UT Drupal Kit.

## Decision

Create a document that defines [what we maintain and what sites can modify](https://wikis.utexas.edu/display/WCMS/Site+configuration%3A+what+we+maintain%2C+what+sites+can+modify).

- Let's call configuration we fully control "Managed configuration." Use the [Features](https://drupal.org/project/features) module to manage thematic subsets of `yaml` configuration files and to push configuration changes to sites.
- Let's call configuration we install but which sites can modify "Default configuration." Use Drupal's [Configuration API](https://www.drupal.org/docs/drupal-apis/configuration-api/simple-configuration-api) to push configuration changes to these elements without overwriting site-specific modifications.

## Consequences

- Site owners don't have an immediate way in their UI to see which configuration they can or cannot change. See [7. Read-only configuration](0007-read-only-configuration.md).
- Using Features to install and update configuration we manage saves our team time compared to writing programmatic equivalents.

