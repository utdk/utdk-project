# 15. Display variant architecture (view modes)

Date: 2022-12-05

## Status

Accepted

## Context

The UT Drupal Kit provides structured data components, such as "Promo Unit," which have multiple display variants, such as "Landscape," "Square" and "Portrait." Some node types, such as "Profile," also have multiple display variants, such as "Basic," "Name Only," and "Prominent." Drupal best practices encourage using [view modes](https://www.drupal.org/node/1577752) to render display variations such as these.

## Decision

Implement separate view modes for each display variant of an entity.

## Consequences

- Some implementations will require many view modes to represent nominal differences in display. For example, the Hero image component will require distinct view modes to represent whether an image's anchor position should be left, right, or center for each of the 5 available display styles, resulting in 15 view modes.
- Implementing different view modes for each variant will allow the content to correctly use Drupal's render cache, something we discovered in https://github.austin.utexas.edu/eis1-wcs/utnews/issues/230, where we were not using separate view modes for display variants.
