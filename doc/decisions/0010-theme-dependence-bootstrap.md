# 10. Theme dependence (Bootstrap)

Date: 2022-12-05

## Status

Accepted

## Context

Our team wants the Drupal Kit to accommodate sites that want the functional capabilities of the Drupal Kit without the look-and-feel of our [Forty Acres](https://github.austin.utexas.edu/eis1-wcs/forty_acres) theme. Therefore, the Kit should be architected in a way that allows developers to build a compatible theme from the ground up. Toward this end, we acknowledge that:

- Providing a Drupal installation profile that is 100% front-end agnostic is impractical to maintain, and there isn't demand for it among campus developers.
- Building a theme completely from scratch is time-consuming.
- Using a front-end framework such as [Bootstrap](https://getbootstrap.com/) saves time.

## Decision

Architect the UT Drupal Kit installation profile's behavior in a way that doesn't assume the presence of theming from Forty Acres, but do assume the presence of the Bootstrap framework.

## Consequences

- Assuming the presence of the Bootstrap framework allows us to build functionality that would otherwise be much more difficult. This includes vertical and horizontal tabbed content displays present in the Flex List component and the Profile add-on, and the ability to change the container width on Layout Builder sections.
- In practice, very few sites have gone the route of a completely custom theme. Most sites that need a custom look-and-feel can achieve this through targeted CSS (https://housing.utexas.edu/) or by sub-theming Forty Acres (https://soa.utexas.edu). Because of this, goal of theme agnosticism has been mostly of internal value, encouraging principles like separation of concerns.
