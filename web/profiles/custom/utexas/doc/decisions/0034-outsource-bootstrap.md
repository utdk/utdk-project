# 34. Outsource the base Bootstrap build to the Drupal Kit kernel
Date: 2025-03-07

## Status

Accepted; Supplements `0003-bootstrap-libraries`

## Context

- In `0003-bootstrap-libraries` we established that we would provide supplementary Bootstrap Framework libraries (modal, alert, dropdown, etc.) in the Drupal Kit kernel (`utdk_profile`) rather than the theme, under the reasoning that any Bootstrap-based theme or subtheme used by a Drupal Kit site should have these libraries available..
- That decision implicitly established that the Drupal Kit kernel provides Bootstrap functionality, not the theme.
- In `0006-css-first`, we decided to attempt to write Speedway's look and feel in native CSS rather than SCSS. Since we still needed SCSS to compile the customized Bootstrap build, we were left with having SCSS build tooling in Speedway purely for rebuilding the Bootstrap CSS, not our own.


## Decision

It will be the responsibility of the Drupal Kit kernel, rather than individual themes, to provide baseline CSS and the responsive grid system. This baseline will be provided by Bootstrap, the most commonly adopted frontend framework for websites.

## Consequences

- Periodic updates to the Bootstrap base CSS (e.g., when a new version is released) will be done through a build process within the Drupal Kit kernel rather than multiple, individual themes.
- The Speedway theme will start with no inherent requirement for a build process or SCSS, freeing us up to potentially write all of the look and feel directly in CSS.
- The Bootstrap base CSS will be provided by a Drupal library which themes as a dependency, thus allowing themes to opt out of the Bootstrap CSS if needed.
