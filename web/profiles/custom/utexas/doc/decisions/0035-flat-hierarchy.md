# 35. Organize CSS in a flat -- not deep -- hierarchy
Date: 2025-03-07

## Status

Accepted

## Context

- Reflecting on the organizing of theme files in the Forty Acres theme, the team unanimously agreed that the depth of directories, intended to better compartmentalize the contents, had drawbacks. It ended up making it more confusing to find what CSS was responsible for what output. It made it more difficult to understand how the parts interrelated.


## Decision

We will endeavor to adhere to a 'flat' hierarchy, preferencing most files in the top-level theme directory, minimizing nested directories.

## Consequences

- This does not mean that we cannot use any directories to organize CSS. Rather, we want to start by adding CSS files at the top level and wait to put them into subdirectories until the top level organization becomes unwieldy.
- We will adopt a file namespacing convention (see `0009-file-namespacing`) to group files within this flatter hierarchy.
