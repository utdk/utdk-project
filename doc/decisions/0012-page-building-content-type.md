# 12. Page building content type

Date: 2022-12-05

## Status

Accepted

## Context

Released in 2015, UT Drupal Kit version 2 provided two node types with multiple layout templates. "Standard Page" included 8 layouts geared toward content-driven interior pages. "Landing Page" included 3 layouts intended for full-width showcases. In both cases, there were limitations on which content components could be placed in layout regions and how many components could be added.

In general, our customer steering committee felt these constraints were problematic; they did not consider them sensible guardrails. We need a page-building approach that is more flexible and less opinionated.

## Decision

Provide a single, flexible-layout content type, the "Flex page," with no limitations on layout design or on the number and placement of content components.

## Consequences

- Content editors are no longer constrained to a subset of layout choices or components. This will result in fewer site customizations for layout "one-offs,"
- Without templates or limitations, content editors will create pages with inconsistent and/or non-intuitive information architecture.
- There is a way to start from a layout template. Each new page must either be built from scratch or be cloned from an existing page with content.
