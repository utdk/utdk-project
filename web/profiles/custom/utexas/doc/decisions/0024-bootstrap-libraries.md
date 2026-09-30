# 24. Boostrap Libraries

Date: 2024-04-23

## Status

Accepted

## Context

- Since version 2 of the UT Drupal Kit, Forty Acres provided UI-based settings to enable optional Bootstrap libraries for advanced WYSIWYG displays.
- Those libraries provide functionality that is not intrinsic to the Forty Acres theme, and the Forty Acres theme does not require it for its standard functionality.
- Those libraries *are* required by components outside Forty Acres: the "Nav/Tabs" library is a requirement of the UT Drupal Kit's "Flex List" component and the "Profile" add-on.
- We conclude that a UI-based method for enabling these Bootstrap libraries does not make sense any more. Providing the libraries via Forty Acres also doesn't make sense.

## Decision

Relocate the Bootstrap utility libraries from `forty_acres` to `utdk_profile` and load them on every page request, rather than loading them based on a site setting.

## Consequences

- Sites using Forty Acres will still have access to the same Bootstrap libraries.
- Since libraries will always be loaded (rather than controlled through settings), the page weight on every page request will increase.
- Sites using Forty Acres without `utdk_profile` would need to add these libraries themselves. However, no known sites with this setup exist.
