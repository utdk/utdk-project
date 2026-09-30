# 29. Core theme template inheritance

Date: 2025-02-12

## Status

Accepted

## Context

Speedway's base theme is stable9.

## Decision

Drupal's `stable9` theme templates provide a minimal baseline without introducing more complexity than we need. Copy `stable9` templates into Speedway on a case-by-case basis to add useful Drupal CSS classes. Omit any `stable9` theme dependencies in the templates, such as library attachments.

For more complex needs, create our own templates, or rely on custom modules with custom templates.

## Consequences

More CSS classes will be present in page markup, which could be perceived by some developers as 'noise'.
