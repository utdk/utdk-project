# 14. "Smallest Recognizable Component" (SRC) for cards

Date: 2022-12-05

## Status

Accepted

## Context

Many of our [card components](https://www.nngroup.com/articles/cards-component/) allow multiple instances of each component (e.g., Promo Unit, Promo List, Flex Content Area, Social Links).

Displaying these types of components with responsive design is challenging: content editors can place these components in narrow regions, such as a four-column layout, and can additionally set how many instances should display in a horizontal row before stacking. Especially on medium-sized screens, this can result in a no-win situation: if we shrink content to fit the available space, card content can become unreadable; if we stack a card's elements, it can become unrecognizable as a self-contained unit.

Solutions are complicated by the technical reality that CSS media query breakpoints can only target viewport width, not container width (at least of this writing).

## Decision

Provide a visual definition of the "Smallest Recognizable Component" (SRC) for each card component. Design small-device responsiveness for the components so that it respects this definition. For example, if a Promo Unit's SRC is defined as always having the image floated left of text elements, this positioning design must be respected by even the smallest screen width display.

## Consequences

- Card components will be thematically recognizable on any screen width.
- Content editors' item-per-row designation cannot always be honored. We label these designations as "**Limit** to [n] items per row" to indicate that a fewer number of items per row may display depending on the available container width.
