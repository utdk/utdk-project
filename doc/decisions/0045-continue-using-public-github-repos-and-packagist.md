# 45. Continue using public GitHub repos and Packagist

Date: 2026-07-17

## Status

Accepted

## Context

An audit of our public GitHub mirrors and Packagist packages prompted a team discussion about whether to move UTDK repositories to private GitHub repos, distributed to sites via an integrated Composer setup with a Pantheon secret token instead of Packagist.

The only benefit of moving to private repos is to the WCMS team: fewer repos to manage, one less release step (sync to GitHub mirror), and no longer needing to keep Packagist packages in sync with GitHub releases.

That benefit is outweighed by the cost to people outside the WCMS team:

- Self-managed site owners doing custom development would need to generate and manage a GitHub token to pull our packages via Composer, adding friction that doesn't exist today.
- Third-party vendors working on self-managed sites would lose easy access to our source code.

## Decision

We will keep our GitHub repos public and continue publishing packages to Packagist, rather than moving to private repos with integrated Composer.

## Consequences

- Self-managed site owners and third-party vendors keep frictionless, tokenless access to our source code.
- The WCMS team continues to manage multiple public repos and Packagist packages, and keep them in sync during releases, rather than consolidating around a single private upstream.
