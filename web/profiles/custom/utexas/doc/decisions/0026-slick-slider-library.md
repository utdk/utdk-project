# 24. Maintaining a modified version of the Accessible 360 Slick library

Date: 2024-10-14

## Status

Outdated as of 2026-10-01

## Context

- A widely-used carousel/slider solution is the [SlickJS library](https://github.com/kenwheeler/slick). This library was forked into the [Accessible360 Slick library](https://github.com/Accessible360/accessible-slick) to provide accessibility enhancements. Our team adopted the latter library for our Hero Carousel, and some custom sites use it for similar functionality.
-  In 2024, jQuery 4 was released, removing a number of deprecated methods. Neither the original SlickJS library or the Accessible360 library are actively maintained, and a review of discussions and issue activity suggest that they will never provide a release that is jQuery 4 compatible. We therefore need to decide whether to use a different solution that is jQuery 4 compatible or use a modified version of the Accessible 360 Slick library that is jQuery 4 compatible.

## Decision

- Modify the Accessible 360 Slick library to be jQuery 4 compatible and maintain that library internally, provided as part of the UT Drupal Kit kernel.

## Consequences

- Staying with Accessible 360 Slick, rather than adopting a different solution, will reduce short-term effort in refactoring carousel implementations.
- Medium- and long-term, this decision does not preclude a future switch to a different solution
- Maintaining our own version of the library means that there will be more effort involved in fixing bugs in that library, should they occur.
