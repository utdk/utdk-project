# 31. Accessible Mega Menu and Bootstrap Navbar

Date: 2025-02-12

## Status

Accepted

## Context

- As with accessibility for the Drupal Kit in general, we place a high value on making the menu navigation fully accessible
- We are a small team with limited time for -- and limited expertise in -- accessibility navigation rules and implementations
- To our knowledge, there is now an alternative to the [Accessible Mega Menu](https://adobe-accessibility.github.io/Accessible-Mega-Menu/) that can similarly provide all the requirements we have identified (mobile/desktop display, keyboard/mouse/touch navigable, aria-accessible)
- The Accessible Mega Menu is an older fork of the original project that we have customized and maintain ourselves
- There was another issue with the Accessible Mega Menu about being unable to use main menu controls with touch on wide screens that we put off addressing due to our plan to replace Forty Acres with the new Speedway theme

## Decision

Replace the Accessible Mega Menu with the Bootstrap 5 Navbar for main navigation

## Consequences

- The Bootstrap 5 Navbar appears to be able to support the same basic functionality as the Adobe Accessible Mega Menu, and where its specific behavior diverges, small progressive enhancements could replicate the behavior of the Adobe Accessible Mega Menu. The main difference between the libraries is how they handle the expansion of L2 item. The Bootstrap approach takes a more universal approach

