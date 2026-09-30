# 37. Divide development into two phases
Date: 2025-03-07

## Status

Accepted

## Context

- The build of the Speedway theme has two goals. First, we want to support a new look-and-feel for the Drupal Kit. Second, we want to use the build to improve the developer experience compared to Forty Acres: remove unnecessary CSS, improve organization, and consolidate where possible.
- Accomplishing these two goals simultaneously carries risks. Attempting to introduce a new look-and-feel while also rearchitecting the CSS makes it difficult to establish criteria for code review: there is no baseline look-and-feel to confirm that the architectural changes do not introduce regressions or other visual problems.

## Decision

We agree to divide the build of Speedway into two phases. Phase One will rearchitect the structure and definitions of Forty Acres while not attempting to introduce visual changes. Phase Two will introduce visual changes within the rearchitected CSS.

## Consequences

- Review of pull requests during Phase One will be able to have an objective criteria: does the new architecture incorporate all of the responsive accommodations, permutations, and use cases that were painstakingly identified and accounted for in Forty Acres?
- Review of pull requests during Phase Two should largely be able to avoid discussions of architectural organization and focus purely on evaluating the new look-and-feel.
- The time to deliver a usable theme may take longer than if we tried to do everything in one phase.
