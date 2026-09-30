# 33. Use CSS whenever possible instead of SCSS
Date: 2025-03-07

## Status

Accepted; Replaces `0005-rem-calc`

## Context

- When we built Forty Acres, the abstraction layer of SCSS was required to write efficient CSS. Now CSS natively supports the main features we used SCSS for: variables, imports, and nesting.


## Decision

We will start the rebuild of the Speedway theme by endeavoring to write in native CSS, making use of CSS nesting, imports, and variables. We will defer the use of SCSS until such time that it becomes a necessity.

## Consequences

- Writing in CSS means we don't need to retain cognitive familiarity with another abstraction layer for theming.
- We may have to rework our initial CSS if we reach a point where SCSS is required for efficient code.
- By starting with just CSS, we retain the possibility that we may not need a build process (e.g., npm, gulp, postCSS) at all, reducing the amount of tooling we must use and maintain.
