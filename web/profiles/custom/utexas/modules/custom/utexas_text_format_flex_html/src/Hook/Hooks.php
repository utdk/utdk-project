<?php

namespace Drupal\utexas_text_format_flex_html\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_config_schema_info_alter().
   */
  #[Hook('config_schema_info_alter')]
  public function configSchemaInfoAlter(&$definitions) {
    // Do not limit source-editable attributes to those present through the
    // CKEditor toolbar.
    // See https://www.drupal.org/project/drupal/issues/3410100.
    // Note that the below code will apply to ALL text formats, not just
    // Flex HTML.
    // See doc/decisions/0023-ckeditor-source-editing.md.
    if (isset($definitions['ckeditor5.plugin.ckeditor5_sourceEditing'])) {
      unset($definitions['ckeditor5.plugin.ckeditor5_sourceEditing']['mapping']['allowed_tags']['sequence']['constraints']['SourceEditingRedundantTags']);
    }
  }

}
