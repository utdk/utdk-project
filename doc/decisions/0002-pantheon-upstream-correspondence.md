# 2. Pantheon Upstream Correpondence

Date: 2023-01-18

## Status

Accepted

## Context
Pantheon provides a model repository for [Integrated Composer](https://pantheon.io/docs/guides/integrated-composer) upstreams at [pantheon-upstreams/drupal-composer-managed](https://github.com/pantheon-upstreams/drupal-composer-managed). The UT Drupal Kit v3 upstream uses this as its model, but also contains modifications specific to our implementation. Pantheon periodically makes changes to `drupal-composer-managed`; we periodically evaluate these changes for adoption. We want to simplify comparing two divergent repositories.


## Decision
Match the contents, order, and syntax of Pantheon's `drupal-composer-managed` in our upstream wherever its contents, order, and syntax do not need to be different.


## Consequences

1. It will be easier for us to compare, line by line, the differences between Pantheon's model and our implementation.
1. Some of the contents of our repository will be functionally unnecessary, such as the `upstream-require` Composer script, and other elements will be arbitrary, such as the ordering of directives in a `.gitignore` file or in `composer.json` configuration.
