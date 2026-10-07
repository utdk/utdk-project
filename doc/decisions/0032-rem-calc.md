# 32. REM Calc
Date: 2025-02-12

## Status

Replaced by 0006-css-first

## Context

- It is preferable to define CSS using responsive em calculations ("rem"), which allow the target elements to vary proportional to screen pixel density and display zoom factor.
- As human developers of CSS, it is typical for us to think in absolute values, namely pixels, rather than `rem`.


## Decision

~Use the `rem-calc()` SCSS function to allow us to think in pixels but generate end values that are represented in `rem`.~

## Consequences

- Relying on an SCSS function means we cannot abandon SCSS build compilation tooling in favor of writing pure CSS.
- Some developers on our team who have transitioned to thinking in `rem` will find the use of the `rem-calc()` to be more, rather than less, work.
