# UTexas Qualtrics Filter

This is a Drupal module that provides a text format filter to embed Qualtrics forms into WYSIWYG fields.

Full documentation can be found at https://utexas.sharepoint.com/sites/UTDK/SitePages/embedding-qualtrics-forms.aspx


### Expected shortcode syntax for text format filter
Provided shortcode syntax of the following pattern:

```
[embed]URL_HERE | height:HEIGHT_HERE | title:TITLE_HERE[/embed]
```

The text format filter will output the following:

```
<iframe src="URL_HERE" scrolling="auto" name="Qualtrics" title="TITLE_HERE" class="qualtrics-form" id="Qualtrics" width="100%" height="HEIGHT_HERE" frameborder="no" align="middle"></iframe>
```

The title and height values are optional.


## Development and Stewardship Notes for CKEditor plugin
In addition to providing a text format filter that converts shortcode syntax into iframe markup, this module includes a CKEditor plugin with a form, in a modal, for inputting Qualtrics information.

### Read first
- Drupal's CKEditor 5 architecture: https://api.drupal.org/api/drupal/core!modules!ckeditor5!ckeditor5.api.php/group/ckeditor5_architecture/10.0.x
- Drupal's API for working with CKEditor 5: https://www.drupal.org/docs/drupal-apis/ckeditor-5-api/overview
- "CKEditor 5 Dev Tools" (https://www.drupal.org/project/ckeditor5_dev/) includes a starter template with useful inline comments about naming.

The CKEditor 5 version of this plugin was created by the WCMS team in 2022. The plugin itself is largely based on the example [Abbreviation Plugin, version 2](https://ckeditor.com/docs/ckeditor5/latest/framework/guides/plugins/simple-plugin/abbreviation-plugin-level-2.html).

While plugins can be written in plain JavaScript, all documentation uses Typescript. The Typescript files must be compiled for use as a plugin, and require a specific compilation process to be integrated as a standalone plugin within Drupal's CKEditor instance.

Custom plugins can be developed outside of the context of Drupal following the model at [developing custom plugins](https://ckeditor.com/docs/ckeditor5/latest/framework/guides/plugins/creating-simple-plugin-timestamp.html#lets-start). A [custom inspector](https://ckeditor.com/docs/ckeditor5/latest/framework/guides/development-tools.html) to help examine the model and view of the CKEditor interface. **However, for integration within Drupal, the compilation process provided in the generic CKEditor documentation will not work; rather, you need a [DLL build](https://ckeditor.com/docs/ckeditor5/latest/installation/advanced/alternative-setups/dll-builds.html) that will create a standalone plugin.**

### Development
To make modifications to the Qualtrics plugin itself:

1. Add and install this module in a Drupal site as you would normally.
2. cd into `js/ckeditor5_plugins/qualtrics`
3. Make desired changes in the `src/` directory.
4. Run the following commands to build the distributable JS:

```
npm install
yarn install
yarn run build
```

5. Run `drush cr`

### Architectural overview

```
├── index.js --> Entrypoint for build process (nonfunctional)
├── src
│   ├── qualtricsform.js --> Business logic for rendering & validating form
│   └── qualtrics.js --> Business logic for displaying the button & downcasting
└── theme
    ├── css
    │   └── form.css --> Minor tweaks to the form in the modal
    └── icons
        └── qualtrics.svg --> The plugin icon
├── build
│   └── qualtrics.js --> The DLL, used by CKEditor
```
