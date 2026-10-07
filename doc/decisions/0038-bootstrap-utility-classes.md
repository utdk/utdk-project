# 38. Use Bootstrap Utility classes
Date: 2025-03-07

## Status

Accepted

## Context

- The CSS of Forty Acres includes a large amount of custom CSS for responsiveness, padding, and layout that could have been achieved through the numerous utility classes by the Bootstrap framework.

## Decision

We will try to use more Bootstrap utility classes over writing customized CSS for things like responsive behavior, padding, margins, and layout.

## Consequences

- We should be able to reduce the amount of custom CSS, saving ourselves maintenance work in the long term
- Refactoring the look-and-feel to use Bootstrap utility classes will require more time in the short term, evaluating that these changes maintain the intended layout and responsiveness of Forty Acres.
