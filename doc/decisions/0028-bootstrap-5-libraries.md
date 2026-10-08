# 28. Bootstrap utility library provisions

Date: 2024-10-21

## Status

Accepted; Revises #24

## Context

- Beyond the foundational grid, Bootstrap provides utility libraries for things like accordions, modals, and alerts. Till now, we have provided a subset of the available libraries, picking out individual JS files from the Bootstrap distribution. This architectural decision was originally meant to allow developers to opt in to specific libraries through Forty Acres' theme settings, keep page load size as small as possible.
- With the relocation of Bootstrap libraries to the kernel (see ADR #24), we decided to load all of the libraries on all pages, rather than requiring developers to pick and choose, as the difference in page load sizes was trivial.
- Bootstrap 5 moved away from shipping distributable versions of individual libraries in favor of a single `bootstrap.bundle.js`, and also reduced the overall size of the libraries. We should decide whether or not to include all of the Bootstrap utility JS, rather than a subset.

## Decision

- Include the single `bootstrap.bundle.min.js` in the build of all pages.

## Consequences

- The total page load size will not change materially, increasing to 110 KB from 87 KB, where typical Drupal Kit site pages' total size is between 1-2 MB.
- It will be easier for our team to update to future minor version releases of Bootstrap 5
- Developers will now have access to new utility libraries for Button, Carousel, OffCanvas, Scrollspy, and Toast, where previously they only had access to Alert, Collapse, Dropdown, Modal, Tab, and Tooltip.
- This may reduce confusion for developers who expect the entire Bootstrap utilities to be available via the Kit.
- We would no longer be in a position to deprecate or remove individual Bootstrap utility libraries.
