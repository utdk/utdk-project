# 13. Multi-field component architecture

Date: 2022-12-05

## Status

Accepted

## Context

Version 2 of the Drupal Kit introduced a set of multi-field structured-data content components. These were implemented as custom compound field types because Drupal's entity API at the time had no alternatives. Drupal 8's rebuild of entity design brings two ways to build multi-field components within the UI. Content Blocks are now entities which can have their own fields. In contributed code, the Paragraphs module provides an entity type geared towards assembling multiple fields.

However, both of these approaches rely on entity references stored in relational tables that can result in database bloat. This problem is summarized in Jakob Perry's [Entity References gone wild: How relationships can sink your project](https://www.youtube.com/watch?v=jKh8HuECm3g).

## Decision

Architect the multi-field structured-data content component as a custom compound field. Add the custom field type to a custom Content Block type so that the component can be used either with Layout Builder or as a standalone reusable block.

## Consequences

- Building and maintaining these components will take more time.
- We will prevent a scenario of creating sites with unsustainable database bloat that have no avenue for remediation.
