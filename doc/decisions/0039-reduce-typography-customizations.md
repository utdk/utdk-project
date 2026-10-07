# 39. Prefer Bootstrap Reboot over subtle typography customizations
Date: 2025-03-27

## Status

Accepted
Refines "Divide development into two phases" (0010)

## Context

- The CSS of Forty Acres includes a large amount of subtle CSS overrides to typography, specifically line-height, font-size and margins, as shown in the examples below:

```css
h5 {
  font-size: 1rem;
  line-height: 1.5625;
  font-weight: 600;
}
blockquote * {
  margin-bottom: 0;
}

address {
  margin: 0 0 1.5em;
}
```

- Bootstrap provides a well thought-out "Reboot" that provides a good starting point these kind of typographical elements.

## Decision

Instead of replicating the typography-related overrides from Forty Acres, prefer whenever possible to rely on the Bootstrap default Reboot choices. CSS attribute declarations like the examples above should be omitted from Speedway CSS unless a visual review establishes a substantial need for them.

This refines our previous decision in "Divide development into two phases" (0010). In that ADR, we stated:

> Phase One will rearchitect the structure and definitions of Forty Acres while not attempting to introduce visual changes.

Phase One will still focus on incorporating the "painstakingly identified" use cases from Forty Acres CSS, but will also be open to minor visual changes related to the typography when it allows us to simplify the CSS without negatively affecting the intent of the Forty Acres design.

This does not apply to headings 1-6. Headings will continue to use custom font sizes as set in Forty Acres to show a greater visual differnce between headings.

## Consequences

- We will be able to simplify and reduce the CSS included in the Speedway theme compared to what is in Forty Acres.
- It will take more time to review code changes, as each porting task will likely involve developer judgment calls about what CSS can be removed, and will take more time for the reviewer to confirm that any typographical visual changes are not problematic.
