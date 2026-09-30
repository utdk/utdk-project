# 30. Bootstrap Libraries

Date: 2025-02-12

## Status

Accepted

## Context

- In line with our previous thinking, the Bootstrap libraries should not be located in our theme. See utdk_profile/doc/decisions/0024-bootstrap-libraries.md for more context.

## Decision

Leave the Bootstrap utility libraries in `utdk_profile` and load them on every page request, rather than loading them based on a site setting.

## Consequences

- Sites using Speedway without `utdk_profile` would need to add these libraries themselves. However, no known sites with this setup exist.
