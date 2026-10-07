# 20. Default content installation

Date: 2022-12-05

## Status

Accepted

## Context

The presence of default (or "demo") content on a new site is one way to help content editors figure out how to create and modify content. At the same time, for migrated sites or sites with users already familiar with content editing, default content is undesirable.

We need a way to make default content optional during installation.

## Decision

- Provide a site installation user interface and command-line option for toggling default content that includes header, main menu, and footer content.
- Provide separate "Demo content" modules in add-on packages whose purpose is solely to install initial content examples.

## Consequences

- Sites can choose whether to have default content, and this choice can be scripted during automated provisioning.
