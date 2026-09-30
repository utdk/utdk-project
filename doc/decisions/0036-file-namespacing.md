# 36. Adopt a file namespacing convention for organization
Date: 2025-03-07

## Status

Accepted

## Context

- Given our decision in `0008-flat-hierarchy` to reduce the number of subdirectories used to organize CSS, we will have many more files in the top-level CSS directory, posing an organization challenge of its own.

## Decision

We will adopt a file namespacing convention, evolving as we build the theme CSS, that helps group CSS files into categories.

## Consequences

- Scanning the top-level CSS directory will allow us to see all/most of the CSS involved while also being able to focus on groups of CSS. An example (not the finalized convention) is shown below:

```
├── css
│   ├── component--featured-higlight.css
│   ├── component--hero-carousel.css
│   ├── component--hero.css
│   ├── component--promo-list.css
│   ├── component--promo-unit.css
│   ├── component--resources.css
│   ├── drupal--alignment-overrides.css
│   ├── drupal--layout-builder.css
│   ├── drupal--media-library.css
│   ├── drupal--pager.css
│   ├── layout--brandbar.css
│   ├── layout--footer.css
│   ├── layout--header.css
│   ├── layout--menus.css
│   ├── layout--sidebars.css
│   ├── typography--base.css
│   ├── typography--buttons.css
│   ├── typography--facets.css
│   ├── typography--fonts.css
│   ├── typography--horizontal-tabs.css
│   ├── typography--images.css
│   ├── typography--links.css
│   ├── typography--lists.css
│   ├── typography--tables.css
│   ├── utevent
│   ├── utnews
│   └── utprof
```
