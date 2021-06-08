## Purpose
Fixes #

## Vouches
- [ ] I checked the changed files for syntax compliance & [naming conventions](https://github.austin.utexas.edu/eis1-wcs/d8-standards/blob/master/Naming_Conventions.md) adherence
- [ ] I considered whether documentation or a decision record needs to be added
- [ ] I considered whether this change accommodates new and existing sites

## Callouts
- [ ] This change has a companion documentation task: #
- [ ] Combo change (requires changes from another repo):

## Reference: running tests locally
1. `fin test web/profiles/contrib/utexas/tests/src/Functional`
1. `fin test-js web/profiles/contrib/utexas/tests/src/FunctionalJavascript`

## Setup
```
git clone --branch <branchname> git@github.austin.utexas.edu:eis1-wcs/utdk-project.git \
  && cd utdk-project \
  && (cd upstream-configuration && composer require utexas/utdk_profile:dev-develop --no-update) \
  && composer require utexas/utdk_localdev:dev-master \
  && fin init && fin init-site --wcs
```

## Acceptance criteria
