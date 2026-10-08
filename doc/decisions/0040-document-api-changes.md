# 40. Document API changes between Forty Acres and Speedway
Date: 2025-05-06

## Status

Accepted

## Context

- Forty Acres includes a "CSS API" of sorts, a collection of utility classes that allow content editors and developers to use out-of-the-box styling. In auditing this CSS we have identified elements that are either underutilized, provide comparatively little value, or have equivalents in Bootstrap utility classes. These are candidates for removal in Speedway.

## Decision

When we identify parts of Forty Acres utility CSS that we deem not worth porting to Speedway, we will document this as an API change so that people transitioning from Forty Acres to Speedway will be able to perform a comprehensive audit in case they need to adjust to account for these changes.

## Consequences

- Our team may have to spend more time discussing whether or not a given utility class should be ported. For example, the team already had a discussion about whether or not to include button variants for square and pill design. In that case, while we agreed that these styles were likely rarely used on sites, the amount of CSS involved was so little as to not benefit us much by removing it. This instance illustrates that to some extent each decision will need to be evaluated on a case-by-case basis.
- Switching from Forty Acres to Speedway may require content editors and developers to make changes to maintain the look and feel of specific elements.
- We will be able to remove some Forty Acres CSS, simplifying our maintenance and development while responsibly accounting for changes that could impact sites.

