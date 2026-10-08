# Speedway

This is a Drupal theme maintained by the University of Texas at Austin for use with the UT Drupal Kit. It is based on the Bootstrap library v5.

Full documentation can be found at https://utexas.sharepoint.com/sites/UTDK/SitePages/configuration.aspx#theme-settings

## Local development (internal, WCMS Team)

Important elements of local development for introducing changes in the Speedway theme:

- Need the latest development snapshot of `utdk_profile` to make sure that preprocess functions, templates, etc., provided by `utdk_profile` are present in your codebase for use with `speedway`
- Need realistic demo content for testing.

The easiest setup that provides the above is to develop from the `speedway-nightly` site, which pulls in the latest development snapshots nightly, and sets the active theme to `speedway` for you.

### Initial setup

This will give you a new, working copy of Speedway Nightly:
```
siteclone speedway-nightly
lando start
lando pull
lando drush uli
```

### Develop with Speedway theme

```
cd web/themes/custom
rm -rf speedway
austinclone speedway
git checkout -b <my branch>
```

### Updating a previously installed `speedway-nightly`

If you have a local copy of Speedway Nightly already, before doing new development, you should pull the latest nightly commit(s) from Pantheon and update the dependencies:

```
git pull pantheon master
lando composer install
```

### Refreshing demo content

Since `speedway-nightly` is a site on Pantheon, you can refresh your local demo content easily:

```
lando pull
```

## Stylelint

```
nvm use 20
npm install
npm stylelint <path/to/file>

# To automatically fix eligible items
npm stylelint --fix <path/to/file>
```
