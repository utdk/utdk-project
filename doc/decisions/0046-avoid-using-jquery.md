# 46. Avoid using jQuery

Date: 2026-09-08

## Status

Accepted

## Context

Drupal core has made a plan to stop using jQuery (https://www.drupal.org/project/drupal/issues/3052002).

## Decision

New code that involves JavaScript should avoid using jQuery whenever practicable; when existing code that uses jQuery needs to be changed, that code should be refactored to use vanilla JavaScript. Use resources like https://youmightnotneedjquery.com/ and https://css-tricks.com/now-ever-might-not-need-jquery/ to facilitate transliteration of jQuery into JavaScript.

## Consequences

- When Drupal core eventually stops including jQuery, it will be less work for us to update.
- It may take more time and more lines of code to generate vanilla JavaScript equivalents of what could be done with jQuery helper methods.
