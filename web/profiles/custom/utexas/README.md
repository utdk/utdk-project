# UT Drupal Kit Installation Profile

This is a Drupal installation profile that serves as the 'kernel' of the UT Drupal Kit.

Full documentation can be found at https://utexas.sharepoint.com/sites/UTDK


## Coding Standards and Architecture Overview

- [Coding syntax standards](#coding-syntax-standards)
  - [Making exceptions for (ignoring) individual coding standards](#making-exceptions-for-ignoring-individual-coding-standards)
    - [Policy for "...calls should be avoided in classes, use dependency injection instead..."](#policy-for-calls-should-be-avoided-in-classes-use-dependency-injection-instead)
- [Filesystem Conventions](#filesystem-conventions)
- [Versioning guidelines](#versioning-guidelines)
  - [Version constraints for dependencies](#version-constraints-for-dependencies)
  - [Tool for checking version constraint compatibility](#tool-for-checking-version-constraint-compatibility)
  - [Version increments (major/minor/patch)](#version-increments-majorminorpatch)
- [Naming Conventions](#naming-conventions)
  - [Responsive Image Naming Convention](#responsive-image-naming-convention)
    - [Widget Descriptors](#widget-descriptors)
    - [Semantic Descriptors](#semantic-descriptors)
    - [Examples](#examples)
- [Architecture overview](#architecture-overview)
- [utdk-project as the "upstream"](#utdk-project-as-the-upstream)
  - [Purpose of the upstream](#purpose-of-the-upstream)
- [Our settings.php approach](#our-settingsphp-approach)
- [Our .gitignore approach](#our-gitignore-approach)
  - [The site root uses the `/web/` directory](#the-site-root-uses-the-web-directory)
  - [Both `/contrib/` and `/custom/` directories are ignored](#both-contrib-and-custom-directories-are-ignored)
  - [Scaffolded files are ignored](#scaffolded-files-are-ignored)
  - [Items intentionally not ignored](#items-intentionally-not-ignored)
- [Glossary of Terms](#glossary-of-terms)
  - [Add-ons](#add-ons)
  - [Features](#features)
  - [Composer-based scaffolding](#composer-based-scaffolding)
  - [Exact version constraint](#exact-version-constraint)
  - [Kernel of the UT Drupal Kit](#kernel-of-the-ut-drupal-kit)
  - [Next significant release operator](#next-significant-release-operator)
  - [Root package](#root-package)
  - [Upstream-based scaffolding](#upstream-based-scaffolding)
- [Anatomy of the composer.json file](#anatomy-of-the-composerjson-file)
  - ["name": "utexas/utdk-project"](#name-utexasutdk-project)
  - ["version": ""](#version)
  - ["type": "project"](#type-project)
  - ["license": ["GPL-2.0-or-later"]](#license-gpl-20-or-later)
  - ["repositories"](#repositories)
  - ["require"](#require)
  - ["conflict"](#conflict)
  - ["minimum-stability": "dev"](#minimum-stability-dev)
  - ["prefer-stable": true](#prefer-stable-true)
  - ["enable-patching": true](#enable-patching-true)
  - [Core Composer Scaffold](#core-composer-scaffold)
    - ["allowed-packages"](#allowed-packages)
    - ["web-root": "./web"](#web-root-web)
  - ["file-mapping"](#file-mapping)
  - [composer/installers "installer-paths"](#composerinstallers-installer-paths)
  - ["optimize-autoloader": true,](#optimize-autoloader-true)
  - ["preferred-install": "dist"](#preferred-install-dist)
  - [Platform "php"](#platform-php)
  - ["process-timeout": 0](#process-timeout-0)
  - ["sort-packages": true](#sort-packages-true)
  - [require-dev "drush/drush": "^10"](#require-dev-drushdrush-10)

### Coding syntax standards

Unless otherwise specified, follow [Drupal Coding Standards](https://project.pages.drupalcode.org/coding_standards/).

- For a code change to be approved, it must contain no coding standard violations (PHP, CSS, JS). See our policy for making exceptions, below.
- Occasionally, there will be a pre-existing syntax violation in a file you change during a pull request. Our policy is that you should fix that violation as part of your pull request unless doing so poses an undue burden.
- We use PHPCS and Intelephense to find PHP coding violations within Visual Studio Code. See [Drupal Team VS Code Default Settings](https://github.com/utexas-utdk/drupal_team_vs_code_settings)

#### Making exceptions for (ignoring) individual coding standards

In rare cases, an automated syntax check is wrong or doesn't apply. In these cases, an exception can be made, but the exception must be explained. The `phpcs:ignore` syntax tells the linter to skip the line:

```php
// This serialized data is trusted from the component,
// so we do not restrict object types in unserialize().
// phpcs:ignore
$promo_unit_items = !empty($items[$delta]->promo_unit_items) ? unserialize($items[$delta]->promo_unit_items) : [];
```

##### Policy for "...calls should be avoided in classes, use dependency injection instead..."

1. The PHPCS standard relating to "...calls should be avoided in classes, use dependency injection instead..." is — just like all of the other PHPCS standards — a style-guide recommendation, rather than something that will cause a change in how the code executes.
2. Dependency injection is not impactful for how we use Drupal code, given that we don't write Unit or Kernel tests that only bootstrap parts of the PHP code.
3. Dependency injection is more complicated code than static calls, and therefore has a negative Developer Experience (DX) for our team.
4. Therefore, it's been our recent practice to mark these PHPCS standards as ignored, rather than converting the code to dependency injection.

### Filesystem Conventions

Follow the same conventions we recommend to UTDK developers at <https://utexas.sharepoint.com/sites/UTDK/SitePages/Developers/add-site-specific-module-or-theme.aspx>. The short version:

- Contributed projects from [drupal.org](http://drupal.org) are located in `modules/contrib`, `themes/contrib`, and `profiles/contrib`
- Custom projects hosted via Composer go in `modules/custom`, `themes/custom`, and `profiles/custom`
- Custom projects specific to the site are located in `modules/<site-namespace>`, `themes/<site-namespace>`, and `profiles/<site-namespace>`

### Versioning guidelines

All projects in the UTDK 3+ project family shall use Semantic Versioning, i.e. `MAJOR.MINOR.PATCH`. See <https://semver.org> for a full explanation of the differences between the various version levels.

The format for pre-release versions (e.g. alpha, beta, rc) shall be `MAJOR.MINOR.PATCH-IDENTIFIER`, where `IDENTIFIER` does not include dot-separated identifiers (e.g. `3.0.0-alpha1`). This follows Drupal naming conventions. Please see [https://semver.org/#spec-item-9](https://semver.org/#spec-item-9) for more details.

**Examples:**

- `3.0.0-beta1` - First beta release for the eventual `3.0.0` release.
- `3.1.0` - First minor release after `3.0.0`
- `4.0.0` - First major release after `3.x.x`

#### Version constraints for dependencies

For dependencies required by our project, in most cases we use exact version constraints. Read why: <https://github.com/utexas-utdk/utdk_profile/blob/develop/doc/decisions/0009-dependency-version-constraints.md>

#### Tool for checking version constraint compatibility

<https://semver.madewithlove.com/>

#### Version increments (major/minor/patch)

We release new minor versions of the Drupal Kit on a bimonthly schedule that include any new features or non-urgent bugfixes.

If there is a security update or a critical bugfix, we will create a patch-level release as soon as we can.

A new major version release of the Drupal Kit would only be triggered by an extraordinary event such as something that would require data migration from a previous version of the Kit.

For the contributed Drupal modules we maintain, we follow Drupal's criteria for major/minor/patch versions: [https://www.drupal.org/docs/develop/managing-a-drupalorg-theme-module-or-distribution-project/maintainership/managing-branches-releases#s-when-to-make-new-release](https://www.drupal.org/docs/develop/managing-a-drupalorg-theme-module-or-distribution-project/maintainership/managing-branches-releases#s-when-to-make-new-release)

### Naming Conventions

For all projects, naming things starts with a project-wide unique machine name prefix. Examples:

- In the case of the a custom site, such as Eureka, the project prefix is `eureka`.
- In the case of the UTexas Profile functionality, the prefix is `utprof`.
- In the case of the UT Drupal Kit kernel, the prefix is `utexas`.

| Component | Rules/pattern | Example | Label |
|---|---|---|---|
| Pantheon site name | Lower case alphabetical and hyphens (kebab-case). **No underscores**. Avoid acronyms if possible. There is no hard rule about using service identifiers such as "utdk" or "ic." | `utexas-biology` | |
| Git repository | Site repositories: (kebab-case) shortest non-acronym name that differentiates from other repositories. Packagist-hosted repositories: (snake_case) name that clearly identifies its role | Site repository: `utexas-uex`<br>Packagist-hosted repository: `utexas_saml_auth_helper` | |
| Git Branch | (hyphenated) Issue Tracker Number plus text description | `192-fix-spacing`<br>(**Note:** this format is a technical necessity: branches that are just numbers will be understood by Composer as release versions) | |
| Issue title | A "bug" type issue should describe the bug. An "enhancement" type issue should describe the enhancement. | Bug type issue: `Spacing between vertical tabs on Flex Page is uneven`<br>Enhancement type issue: `Allow content editors to set image focal points`<br>(Note that *neither* should attempt to define the technical resolution) | |
| Pull request title | Issue Number - Issue Title (verbatim) | `1635 - Spacing between vertical tabs on Flex Page is uneven` | |
| Site Namespace Prefix | (snake_case) (prefix for all custom elements) ideally one word | `utexas`, `uteach`, `eureka` | |
| Install Profile | (snake_case) Site Namespace Prefix + "install" | `utexas_install` | UTexas |
| Custom Module | (snake_case) Site Namespace Prefix + component category + specific component | `utexas_role_flex_page_editor` | UTexas Role Flex Page Editor, UTexas Paragraph : Promo Units |
| Custom Content Type Module | (snake_case) Site Namespace Prefix + "content_type" + name | `utexas_content_type_flex_page` | UTexas Flex Page |
| Features | (snake_case) Site Namespace Prefix + component category + specific component | `utexas_paragraph_pca` | UTexas Paragraph : Photo Content Area |
| Custom Content Type | (snake_case) Site Namespace Prefix + name | `utexas_flex_page` | Flex Page |
| Theme | (snake_case) Site Namespace Prefix + "_theme" | `utexas_theme`, `eureka_theme` | UTexas Theme, Eureka Theme |
| Paragraph Type | (snake_case) Site Namespace Prefix + "paragraph" + name | `utexas_paragraph_name` | Photo Content Area |
| Custom Block Type | (snake_case) Site Namespace Prefix + "block" + name | `utexas_block_hero_image` | UTexas Hero Image |
| Field | (snake_case, 32 characters max) `field_` + project prefix + field descriptor | `field_utprof_bldg_room_num` `field_utexas_call_to_action_link` | Link, Copy, Headline |
| View | (snake_case) Site Namespace Prefix + "view" + view type + descriptor | `utexas_view_search_curriculum_resources` | Search - Curriculum Resources |
| Context | (snake_case) Site Namespace Prefix + component type + descriptor | `utexas_search_facets_sidebar_block` | Search Facets Sidebar Block |
| Image Style | (snake_case) Site Namespace Prefix + "image_style" + size (with "w" and/or "h" after) | `utexas_image_style_640w` (image style with width only) `utexas_image_style_500w_333h` (image style with a width and height) | 640w, 500w x 333h |
| Responsive Image Style | (snake_case) Site Namespace Prefix + "responsive_image" + Widget name acronym | `utexas_responsive_image_fca`<br>`utexas_responsive_image_il` | Flex Content Area, Image Link |
| View mode | (snake_case) Site Namespace Prefix + component type + incremental number starting from 1 | `utexas_featured_highlight_1`<br>`utexas_featured_highlight_2` | `Dark Background` `Light Background` (Labels are unrelated to machine names) |
| Release and hotfix branches | Follow [NVIE Gitflow](https://nvie.com/posts/a-successful-git-branching-model/), so `release-x.x.x` or `hotfix-x.x.x` | release-1.1.0, hotfix-2.1.1<br>(Note: it has generally been our convention that regular releases are minor version increments and hotfix releases are patch-level increments, but there will be exceptions.) | |
| Tags | Use [Semantic Versioning](https://semver.org), i.e. `MAJOR.MINOR.PATCH`. For pre-release versions (e.g. alpha, beta, rc) use `MAJOR.MINOR.PATCH-IDENTIFIER`, following the [Drupal naming convention](https://www.drupal.org/docs/develop/git/git-for-drupal-project-maintainers/release-naming-conventions#release-tags). | - `3.0.0-beta1` - First beta release for an upcoming `3.0.0` release.<br>- `3.1.0` - First minor version release after `3.0.0`<br>- `4.0.0` - First major version release after `3.x.x`<br>- **Do not precede with "v" (e.g., v3.1.0)** | |

#### Responsive Image Naming Convention

To create a responsive image style:

1. Use the existing "UTexas" breakpoint group
2. Start (prefix) the machine name with `utexas_responsive_image_`
3. Construct the machine name as `prefix` + `widget descriptor` + `semantic descriptor` (snake_case)
4. Construct the label as `Widget descriptor label` + `Semantic descriptor label` (each word capitalized, separated by spaces, no abbreviations)
5. Assign single, new or existing image styles to each multiplier within each breakpoint provided by the "UTexas" breakpoint group.

##### Widget Descriptors

| Label | Machine name |
|---|---|
| Promo Unit | `pu` |
| Flex content area | `fca` |
| Photo Content Image | `pca` |
| Image Link | `il` |

Promo Unit uses the legacy `pu` widget descriptor in existing installed configuration.

##### Semantic Descriptors

| Label | Machine Name | Example |
|---|---|---|
| Landscape | `landscape` | `utexas_responsive_image_pu_landscape` |
| Portrait | `portrait` | `utexas_responsive_image_pu_portrait` |
| Square | `square` | `utexas_responsive_image_pu_square` |

##### Examples

| Label | Machine Name |
|---|---|
| Promo Unit Landscape | `utexas_responsive_image_pu_landscape` |
| Promo Unit Portrait | `utexas_responsive_image_pu_portrait` |
| Promo Unit Square | `utexas_responsive_image_pu_square` |
| Photo Content Area | `utexas_responsive_image_pca` |
| Image Link | `utexas_responsive_image_il` |
| Flex Content Area | `utexas_responsive_image_fca` |

### Architecture Overview

1. The Drupal Kit is assembled using a Composer [project](https://getcomposer.org/doc/04-schema.md#type) called utdk-project that serves as a scaffolding tool for all other code. On the Pantheon hosting service, this constitutes the Drupal Kit "upstream repository," and thus serves as the basis for downstream sites' root `composer.json` file.
2. This utdk-project scaffolding requires a Drupal installation profile, `utdk_profile`, which we also call the "kernel." This contains all of the actual library dependencies, configuration files, and custom code that make up the Drupal Kit.
3. We only guarantee hosting compatibility of the Drupal Kit on the Pantheon hosting service, and some architectural elements assume the use of Pantheon. The Drupal Kit may work on other hosting platforms, but we do not officially support it.
4. The UT Drupal Kit is available publicly at <https://packagist.org/packages/utexas>. This decision was largely motivated by infrastructure limitations on Pantheon in 2021 which prevent the use of authenticated package repositories, but also vastly simplifies the development process by avoiding the requirement for a private access token.
5. When architecture decisions are subjective, we follow the convention established by [Pantheon's upstream repository,](https://github.com/pantheon-upstreams/drupal-composer-managed) and secondarily to Drupal core's [drupal-project](https://github.com/drupal-composer/drupal-project). If we have reason to diverge from those conventions, document it.
6. We follow the `drupal-composer/drupal-project` model of setting installer paths `modules/contrib`, `themes/contrib`, and `profiles/contrib` for publicly provided projects that are intended for general use. Packages typed as [drupal-custom-module](https://www.drupal.org/docs/creating-custom-modules/add-a-composerjson-file#s-define-your-project-as-a-php-package) will be installed in `modules/custom` (and the same for themes and profiles) so that customized code can be provided for multiple sites via Packagist. Custom code that is expected to live only on one site should be placed in the site-specific namespace directories described in the Filesystem Conventions section.
7. Do not [discard-changes](https://getcomposer.org/doc/06-config.md#discard-changes) so that developers who have modified Composer-provided packages won't have those changes reverted. While developers should **not** modify Composer-provided packages, we do this under the principle ["First do no harm"](https://en.wikipedia.org/wiki/Primum_non_nocere).
8. We use [composer-patches](https://github.com/cweagans/composer-patches) to modify code provided by dependencies. Patches are a necessary part of applying legitimate fixes that have not yet been committed to Drupal core. In our architecture, the dependency utdk_profile defines the version of Drupal core, and also defines a number of patches to be applied. Removing `"enable-patching": true` from the root `composer.json` file would prevent these patches from applying.
9. We add `settings.php` as part of the upstream repository. We loudly document in inline comments and in this wiki that the pattern we are recommending is NOT to touch `settings.php` and rather to put all site-specific configuration in `settings.site.php`.
10. We add `pantheon.upstream.yml` as part of the repository directly so that Pantheon sites can receive updates to the upstream settings by 1-click updates.
11. We set the default PHP version in `pantheon.upstream.yml` and use a Composer script to populate the [platform](https://getcomposer.org/doc/06-config.md#platform) setting in the `composer.json` file. We periodically update that value based on PHP support available on Pantheon infrastructure and Drupal core requirements.
12. We set [process-timeout](https://getcomposer.org/doc/06-config.md#process-timeout) to `0` (i.e., never) for reducing potential build failures.
13. We follow Pantheon's approach to set `composer-exit-on-patch-failure": true` so that SaaS-type site updates **will** fail if a patch we provided does not apply.
14. We make the `.editorconfig` file available as an initial scaffolding file that may be changed or removed by individual sites without consequence.
15. We make the `.gitattributes` file available as an initial scaffolding file that may be changed or removed by individual sites without consequence.

### utdk-project as the "upstream"

The Drupal Kit codebase starts with utdk-project, which is a [Composer "project"](https://getcomposer.org/doc/04-schema.md#type). It uses a local directory, `upstream-configuration/`, to house elements that assemble the UT Drupal Kit kernel and scaffolding. This directory behaves like a Composer package, and has its own `composer.json`. The contents of this directory should not be directly modified unless ITS provides specific [release notes](https://drupalkit.its.utexas.edu/docs/releases/index.html). For more on the technique, see <https://getcomposer.org/doc/05-repositories.md#path>.

#### Purpose of the upstream

Separating the elements that make up the UT Drupal Kit kernel has the advantage of keeping ITS-specific configuration out of the root `composer.json`, which is controlled by an individual site.

If sites need to modify/override the directives in `upstream-configuration`, they can do so with configuration in their [root composer.json](https://github.austin.utexas.edu/eis1-wcs/utdk-project/wiki/Anatomy-of-the-composer.json-file).

### Our settings.php approach

Settings defined in this file are defaults set by ITS that should work for most sites. Developers should not directly modify this file, but rather should use context-specific PHP includes to override or supplement these defaults.

### Our .gitignore approach

The `.gitignore` file in a site repository scaffolded from `utdk-project` is the standard method using Git version control to [specify intentionally untracked files to ignore](https://git-scm.com/docs/gitignore).

It is designed so that, except for uncommon scenarios, **you should never need to change the directives in the file**.

It is also designed around specific ways of scaffolding a Drupal site, based on best practices established in the Drupal community. This page summarizes aspects of that design, so that developers know if and when they might need to modify the `.gitignore`.

#### The site root uses the `/web/` directory

This approach is currently the most common practice for Drupal sites (<https://pantheon.io/docs/nested-docroot>, <https://github.com/symfony/symfony-standard>, and <https://github.com/drupal-composer/drupal-project/blob/9.x/composer.json>).

This behavior is determined by two directives in your `composer.json` file, the [scaffolding setting](https://github.austin.utexas.edu/eis1-wcs/utdk-project/wiki/wiki/Anatomy-of-the-composer.json-file#web-root-web) and the [installer paths settings](https://github.austin.utexas.edu/eis1-wcs/utdk-project/wiki/wiki/Anatomy-of-the-composer.json-file#composerinstallers-installer-paths). The `.gitignore` directives must therefore reflect this, which is why most paths in the file start with `/web/`.

#### Both `/contrib/` and `/custom/` directories are ignored

The default `.gitignore` includes this series of directives:

```
/web/modules/contrib/
/web/themes/contrib/
/web/profiles/contrib/
/web/modules/custom/
/web/themes/custom/
/web/profiles/custom/
```

Code that is installed to any of these directories will be ignored by version control. The `/contrib/` paths are probably intuitive: contributed modules shouldn't need to be committed to the codebase. The `/custom/` paths, however, are a newer practice that may be counterintuitive. Quoting from Pantheon:

> When a development team creates one or more custom modules that are intended for use on more than one site, the typical strategy is to register them in Packagist and give them the type `drupal-custom-module` instead of `drupal-module`. This will cause Composer to install them to the directory `modules/custom`.

In other words, the `/custom/` directories are also reserved for packages that should not be directly versioned in an individual site's codebase.

Custom code that is specific to an individual site should follow the convention of being placed in a directory labelled after a site identifier. For example, a website on faculty seminars could use the site identifier `faculty_seminars`, and would place custom modules, themes, or profiles in the following, which would not be ignored:

```
/web/modules/faculty_seminars/
/web/themes/faculty_seminars/
/web/profiles/faculty_seminars/
```

#### Scaffolded files are ignored

The utdk-project repository uses Drupal's [core-composer-scaffold](https://github.com/drupal/core-composer-scaffold) plugin as a way to retrieve individual files, such as the site `index.php` file, which should not be customized by sites. As such, they also should not be under version control on individual sites. The UT Drupal Kit kernel scaffolds a series of favicons, which are ignored:

```
/web/android-chrome-192x192.png
/web/android-chrome-512x512.png
/web/apple-touch-icon.png
/web/browserconfig.xml
/web/favicon-32x32.png
/web/favicon-48x48.png
/web/favicon.ico
/web/mstile-150x150.png
/web/safari-pinned-tab.svg
/web/site.webmanifest
```

Developers who want to override the default favicons may remove these ignores, replace the files, and commit those replacements to their individual codebase. They will not be overwritten by the upstream subsequently.

#### Items intentionally not ignored

1. We do **not** ignore the `.docksal` directory or the `.lando` file so that developers using these local development tools may commit site-specific directives to the codebase.

### Glossary of Terms

#### Add-ons

UT Drupal Kit add-ons are extra features designed for specific use cases, such as role-based access. They are not included with the base distribution, but are separate packages that must be added to a site's codebase by a developer.

#### Composer-based scaffolding

Files are provided by a scaffolding-enabled package (**Package doing scaffolding**) to another package (**Package being scaffolded**) which holds the scaffolding package as a dependency (or is itself the scaffolding package).

- Source: Package doing scaffolding
- Destination: Package being scaffolded (Site specific repository)
- Update: Files are automatically re-scaffolded any time that the Composer update or install events are fired (see [plugin code](https://github.com/drupal/core-composer-scaffold/blob/4825cb5234c28dff79ad298db582dfb23ff4ca59/Plugin.php#L54-L61)). Note that the Composer events in question should be fired by the site's Composer update procedure.
- VCS: Generally avoided

This method is:

1. Commonly provided by the [core-composer-scaffold](https://github.com/drupal/core-composer-scaffold) Composer plugin
2. Functional highlights ([core-composer-scaffold](https://github.com/drupal/core-composer-scaffold))
   - **Package being scaffolded**:
     - Prevent files from being overwritten by the "package doing scaffolding" (see [Excluding Scaffolding Files](https://github.com/drupal/core-composer-scaffold#excluding-scaffold-files))
     - By default, files that are successfully scaffolded will be automatically added to an appropriate `.gitignore` file relative to their location. (see [gitignore](https://github.com/drupal/core-composer-scaffold#gitignore))
   - **Package doing scaffolding**:
     - Files can be determined to be scaffolded once, not added to a `.gitignore` file and not overwritten by subsequent updates (see [Overwrite](https://github.com/drupal/core-composer-scaffold#overwrite))
   - **Package being scaffolded**/**Package doing scaffolding**:
     - The permission to write scaffolding files can be delegated to individual dependencies, which can also delegate scaffolding permission to individual dependencies, ad nauseum (see [Defining Project Locations](https://github.com/drupal/core-composer-scaffold#defining-project-locations))

#### Exact version constraint

This refers to syntax which tells Composer to install 'this version and version only'. See <https://getcomposer.org/doc/articles/versions.md#exact-version-constraint>

In contrast, developers may use a [next significant release operator](https://github.austin.utexas.edu/eis1-wcs/utdk-project/wiki/Glossary-of-terms#next-significant-release-operator) to specify a range of applicable updates.

#### Features

Features such as utevent, utnews and utprof are included in the base installation as of July 2026. Other [add-ons](https://github.austin.utexas.edu/eis1-wcs/utdk-project/wiki/Glossary-of-terms#add-ons) for specific use cases remain separate for the utdk_profile kernel.

#### Kernel of the UT Drupal Kit

The "kernel" (or "distribution kernel") refers to the components that provide the base installation of the UT Drupal Kit and which provide functionality intrinsic to it. This consists of a Drupal installation profile, as well as a collection of custom modules, a number of contributed module dependencies, and scaffolding files.

The kernel itself is bundled as a single Composer package, [utdk_profile](https://github.com/utdk/utdk_profile).

As of July 2026, the starting theme, Speedway, and the utevent, utnews and utprof [features](https://github.austin.utexas.edu/eis1-wcs/utdk-project/wiki/Glossary-of-terms#features) are also included as part of the kernel.

#### Next significant release operator

This refers to Composer syntax used to specify a range of applicable updates that can be applied. See <https://getcomposer.org/doc/articles/versions.md#next-significant-release-operators>

Using a next significant release operator strategy bypasses setting an [exact version constraint](https://github.austin.utexas.edu/eis1-wcs/utdk-project/wiki/Glossary-of-terms#exact-version-constraint), which will tell Composer to install 'this version and version only'.

#### Root package

The main `composer.json` that defines your project requirements. See <https://getcomposer.org/doc/04-schema.md#root-package>

#### Upstream-based scaffolding

Files are provided in an "upstream repository" already in place in the manner of a starter template.

- Source: Pantheon upstream repository
- Destination: Site specific repository
- Update: Files are updated any time that the **Pantheon upstream repository** is updated and a code update is initialized by a Pantheon user with sufficient permissions. This may be accomplished via the Pantheon Dashboard GUI or through terminus CLI commands.
- VCS: Generally assured by Pantheon upstream update scripts.

### Anatomy of the composer.json file

#### "name": "utexas/utdk-project"

This is a placeholder, and is nonfunctional in the codebase. Individual sites may change this for identification purposes. SaaS-type sites don't need to change this

#### "version": ""

No version key is provided in the root-level `composer.json` file, as this file should reflect the state of the individual site, not the upstream. See the `version` key, below, contained in the `upstream-configuration` directory.

#### "type": "project"

Defining this repository as a project makes it available for use with the `composer create-project` syntax. See <https://getcomposer.org/doc/03-cli.md#create-project>

#### "license": ["GPL-2.0-or-later"]

The GPL-2.0-or-later license is used to be compliant with Drupal Association licensing. See <https://www.drupal.org/about/licensing>

#### "repositories"

The `repositories` key includes:

- <https://packages.drupal.org/8>, which is Drupal's Packagist endpoint for core and contributed projects. See <https://www.drupal.org/docs/develop/using-composer/using-packagesdrupalorg>
- <https://asset-packagist.org>, which allows installation of Bower and NPM packages as native Composer packages.
- `upstream-configuration`, which is a [local repository](https://getcomposer.org/doc/05-repositories.md#path). Its contents are located in this repository, in the `upstream-configuration` directory. That `composer.json` contains the main references to the UT Drupal Kit kernel.

Individual sites are allowed to add more repository references, if needed.

#### "require"

The initial `composer.json` for a site contains a single Composer requirement, `"utexas/upstream-configuration": "*"`. This is a local repository that contains the main references to the UT Drupal Kit kernel. See more below. The asterisk means that any available version or branch is allowed; it does throw a warning when running `composer validate`, but follows the [pattern provided by Pantheon](https://github.com/pantheon-systems/drupal-project/blob/default/composer.json).

#### "conflict"

This key can be used by developers to indicate incompatibility with certain versions of a package. See <https://getcomposer.org/doc/04-schema.md#conflict>. This key includes `"drupal/drupal": "*"` to prevent the monolithic `drupal/drupal` package from being installed alongside the Composer-based core packages.

#### "minimum-stability": "dev"

We follow patterns provided by Pantheon and Drupal here. This setting effectively means that sites can require packages that are not yet marked as "stable". This includes branches as well as tags with `alpha`, `beta`, and `rc` parameters.

#### "prefer-stable": true

Prefer more stable packages over unstable ones when finding compatible stable packages is possible. See <https://getcomposer.org/doc/04-schema.md#prefer-stable>

#### "enable-patching": true

This configuration is required so that required dependencies may, themselves, leverage `composer-patches` and apply patches to other packages. Currently, we apply patches via `utexas/utdk_profile`.

#### Core Composer Scaffold

##### "allowed-packages"

This configuration key relates to Drupal's [Core Composer Scaffold](https://github.com/drupal/core-composer-scaffold), a Composer plugin that facilitates adding files such as the `index.php`, `.htaccess` and `settings.php` to the codebase. These files are typically included in the `.gitignore` file, as they are considered parts of the site build that should not be directly manipulated at the site level. In other words, they are like individual files or directories that have the same Composer relationship to the codebase as a package added as a requirement.

We include the following allowed packages:

- `"pantheon-systems/drupal-integrations"` -- adds Pantheon bits, like the `settings.pantheon.php` file
- `"utexas/utdk_profile"` -- adds favicons, as well as a `settings.php` file, if none exists

##### "web-root": "./web"

This is a Drupal Scaffold-specific metadata key that can be referenced in the file mapping (below). See more at <https://github.com/drupal/core-composer-scaffold#defining-project-locations>

#### "file-mapping"

This tells scaffolding files where to be located. Other projects that leverage Core Composer Scaffold may also define file mapping, as we do in `utexas/utdk_profile`. See more at <https://github.com/drupal/core-composer-scaffold#defining-scaffold-files>

Since we provide scaffolding in our own packages, which presume a `web/` docroot as well, developers who want to use a different docroot would need to manually add new `file-mapping`s to overwrite the ones our packages ship. For example, `utexas/utdk_profile`'s `composer.json` includes `file-mapping` settings that would place the favicon files in the `web/` docroot. Developers would need to copy all of those file mappings into their root-level `composer.json` and override the location.

#### composer/installers "installer-paths"

Composer provides a plugin, `composer/installers`, which allows sites to define where in the project composer packages should be placed. Our default definitions match the convention established in <https://github.com/drupal-composer/drupal-project>. This convention establishes that the site will use a `web/` document root. Developers can technically change this, but would need to do so in conjunction with changes to the Core Composer Scaffold location (see above).

#### "optimize-autoloader": true,

This is a relatively low-impact inclusion. See <https://getcomposer.org/doc/articles/autoloader-optimization.md>

#### "preferred-install": "dist"

This is a relatively low-impact inclusion. See <https://getcomposer.org/doc/03-cli.md#install-i>

#### Platform "php"

This PHP setting is different from setting a PHP minimum version requirement (which we do in upstream-configuration, and which other Composer packages do). It makes the installation act as if the local system is running the specified PHP version, rather than the version it actually has installed. Please read <https://getcomposer.org/doc/06-config.md#platform>

#### "process-timeout": 0

This is a relatively low-impact inclusion. See <https://getcomposer.org/doc/06-config.md#process-timeout>

#### "sort-packages": true

This is a relatively low-impact inclusion. See <https://getcomposer.org/doc/06-config.md#sort-packages>

#### require-dev "drush/drush": "^10"

In contrast to Pantheon's composer project template, which includes `drush` as a regular requirement, we have moved this to `require-dev` so that it can be excluded with the `--no-dev` parameter. Developers who do not use Drush could remove this without any ill effects. We include it in our template so that contracts sites that we manage and which we expect to use Drush locally will have it available.
