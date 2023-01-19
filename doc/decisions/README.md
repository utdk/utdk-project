## Architecture Decision Records
This directory contains important architectural decisions, along with their context and consequences. Useful references:

- https://github.blog/2020-08-13-why-write-adrs/
- https://www.thoughtworks.com/radar/techniques/lightweight-architecture-decision-records
- https://github.com/joelparkerhenderson/architecture_decision_record

## Setup
The ADRs included in this repository are generated using the [adr-tools script](https://github.com/npryce/adr-tools), which can be easily installed following
the instructions provided by the package.

Once installed, new ADRs can be added by typing `adr new <Title of ADR>`.

## Protocols
1. Write ADRs in declarative (present tense) mood.
    - Good: `"Provide separate base helper classes for each custom component migration instead of an abstract class that can be extended."`
    - Bad: `"Team agreed to provide separate base helper classes..."`
    - Bad: `"We should provide separate base helper classes..."`
    - Bad: `"Separate base helper classes were provided..."`.
