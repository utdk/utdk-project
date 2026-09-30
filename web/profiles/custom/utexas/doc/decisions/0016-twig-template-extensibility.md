# 16. Twig template extensibility

Date: 2022-12-05

## Status

Accepted

## Context

Drupal 8 adopted Symfony's [Twig](https://twig.symfony.com/) templating system for rendering content. Twig template architecture can be simple or complex. Developers with limited Twig knowledge can create self-contained templates that only contain HTML and handlebar-style variable placeholders. Advanced developers can use template inheritance with `includes` and `extends` statements as well as conditionals and loops.

We need to decide on the appropriate level of complexity for our developer base.

## Decision

Embrace the gamut of advanced Twig templating syntax, including inheritance and conditionals.

## Consequences

- Campus developers with limited knowledge of inheritance in general and Twig in specific will have a difficult time overriding our base templates. We choose to accept this because, in reality, there are few campus developers customizing Drupal Kit 3 who fit this description. Drupal Kit 2 did have a set of developers who limited their customization primarily to modifying PHPTemplate files and CSS, but the main users of Drupal Kit 3 have been our own team.
- By leveraging inheritance in our Twig templates, we will reduce duplicative declarations across similar files (i.e., Don't Repeat Yourself).
