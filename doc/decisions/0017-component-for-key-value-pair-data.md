# 17. Component for key-value pair data

Date: 2022-12-05

## Status

Accepted

## Context

There are a number of popular content display formats that can be referred to as "key-value pairs." For example, accordions are key-value pairs of title + body content, as are horizontally or vertically tabbed content and HTML definition lists. While these formats display differently, they share the same underlying data structure.

## Decision

Architect a single component, the "Flex list," in which content editors can input key-value pair data and choose from multiple display outputs.

## Consequences

- Content editors can switch the same data between displays without having to re-enter the data.
- A single component called "Flex list" may not be intuitive. Content editors may not know where to go when they are trying to create, say, accordion content.
