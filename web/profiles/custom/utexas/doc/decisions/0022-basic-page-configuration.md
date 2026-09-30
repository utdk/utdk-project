# 22. Basic page content type configuration

Date: 2023-09-21

## Status

Accepted

## Context

- The "Basic page" (machine name: `page`) content type is not an inherent feature of Drupal core, but is part of Drupal's "Standard" installation profile. It would have been possible to create the UT Drupal Kit distribution without a "Basic page" content type.
- A decision was made to include "Basic page" as part of the UT Drupal Kit's installation profile in order to meet the assumed expectations of experienced Drupal developers and site builders.
- The default configuration for the "Basic page" content type was copied to the UT Drupal Kit's installation profile from Drupal's "Standard" installation profile.
- Since the launch of UT Drupal Kit 3, none of the customizations or affordances added to the other custom content types in the UT Drupal Kit have been added to the "Basic page" content type, in order to maintain the goal of this content type's configuration matching a non-UT Drupal Kit installation.
- These configuration differences mean that content editors encounter a different interface on the node edit form for "Basic page" than for other content types included in the UT Drupal Kit.

## Decision

- We will not modify the "Basic page" configuration.
- By not altering the configuration to the "Basic page" content type we eliminate the risk of having our configuration conflict with that of other Drupal Kit developers.
- The "Basic page" node type may inherit configuration that is not directly defined within its configuration files, such as a content moderation workflow, to provide a more consistent content editing experience; these configurations should apply to new and existing sites.

## Consequences

- Other UT Drupal Kit developers can make configuration changes to the "Basic page" content type without fear of those configurations being overwritten in the future.
- Experienced content editors will experience a more consistent user interface across all of the content types installed as part of the UT Drupal Kit though they may not initially understand why the "Basic page" content type has options which are not available in a standard Drupal installation.

