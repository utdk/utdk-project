# 8. Site-as-a-Service management (utdk_saas)

Date: 2022-12-05

## Status

Accepted

## Context

The Drupal Kit is available as [different service offerings](https://ut.service-now.com/sp?id=ut_bs_service_detail&sys_id=d6d65c7c4ff9d200f6897bcd0210c786): "Managed,"	"Custom," and	"Self-managed." All "Managed" sites share a common set of supplementary configuration, such as the presence of a "Site Manager" role, predefined permissions for roles, and SAML login integration. We need a way to install and update this common configuration for "Managed" sites.

## Decision

Create a Drupal module as a standalone Composer package which provides configuration and user-interface tweaks specific to "Managed" sites.

## Consequences

- By packaging this as an asset external to the installation profile, we can avoid installing unnecessary dependencies on non-Managed Drupal Kit sites.
- By packaging this as an asset external to the installation profile, we reduce ambiguity about whether or not a site is in the "Managed" service offering.
