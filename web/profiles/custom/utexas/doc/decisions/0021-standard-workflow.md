# 21. Standard workflow

Date: 2023-07-10

## Status

Accepted

## Context

- Content editors currently have no way to draft content. 
- Users with only the `Content Editor` role cannot unpublish nodes.

## Decision

- All new and existing Drupal Kit sites will have a standard workflow, referred to as the "Standard workflow," which allows content editors to draft, publish, and archive `Basic page` nodes and `Flex Page` nodes.
- "UTDK Custom" and "UTDK Self-Managed" sites will be able to modify or replace this standard workflow.

## Consequences

- Content editors unfamiliar with a workflow that includes draft and archived states may not initially understand why a node is not displaying their latest edits, or why an archived node return "Access denied."
- For content editors who prefer not to use drafting in content creation, they will now have a more complicated user interface that serves, in their minds, no purpose.
- Content editors will be able to more easily experiment with and revise content edits without needing to make those changes public, and will be able to mark content as inaccessible to the general public without having to delete it.
