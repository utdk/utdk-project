# 11. Usage of 'sticky' and 'promoted'

Date: 2022-12-05

## Status

Accepted

## Context

Since the early, simpler days of Drupal, two metadata toggles have been present on content types, "Sticky at top of lists" and "Promoted to front page." For legacy reasons these are still present in Drupal but have been criticized as [hard to understand](https://www.drupal.org/project/drupal/issues/1562804) and there is a desire to [replace them with more meaningful equivalents](https://www.drupal.org/project/drupal/issues/197460). Some of the components of the Kit include functionality similar to "Sticky at top of lists" or "Promoted to front page," such as the add-ons' ability to mark or filter "Featured" items.

## Decision

Do not use Drupal core's "Sticky at top of lists" and "Promoted to front page" in our architecture.

## Consequences

- We will avoid scenarios where these toggles could be used for multiple, conflicting purposes. For example, if we were to use "Promoted to front page" to indicate "Featured" news articles and a developer built a separate Drupal View that filtered by "Promoted to front page," they might get unexpected results.
- We will spend more development time building functionally equivalent metadata toggles rather than leveraging existing pieces.
