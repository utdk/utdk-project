# 23. Source editing in CKEditor

Date: 2024-03-05

## Status

Accepted

## Context

- Drupal 10.2 introduces a new expectation around what can be edited using CKEditor's "Source" mode in conjunction with the "Limit HTML tags" filter: only HTML attributes editable through the CKEditor toolbar are allowed. As a result, for example, the `<a>` tag, if supported in the CKEditor toolbar, will only allow attributes that the CKEditor toolbar allows. There is no way to specify that `<a data-attribute-x>` is also allowed.
- The Drupal Kit has up till now allowed a broader set of attributes, most notably `class` and `id` attributes on many elements.
- There is existing content that includes such attributes.

## Decision

Override the default behavior of Drupal core and continue to allow additional attributes to be manually edited in "Source" mode.

## Consequences

- Existing content on sites will not be lost.
- The Drupal Kit will continue to perpetuate an editorial experience that allows access to manual entry of HTML
- The divergence from the new Drupal core default may lead to confusion for developers in the future.

