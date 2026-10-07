<?php

namespace Drupal\utexas_media_types\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\FileInterface;
use Drupal\utexas_media_types\HookHandler\FileInsertHookHandler;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'field__field_media_oembed_video' => [
        'template' => 'field--field-media-oembed-video',
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments) {
    // Resolve issue in
    // https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/1395.
    $attachments['#attached']['library'][] = 'utexas_media_types/media-oembed';
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, $form_state, $form_id) {
    // Add a suggestion to document upload forms to use a file-sharing service.
    $forms_to_admonish = [
      'media_utexas_document_add_form',
      'views_form_media_library_widget_utexas_document',
      'views_form_media_library_widget_table_utexas_document',
    ];
    if (in_array($form_id, $forms_to_admonish)) {
      \Drupal::messenger()->addWarning($this->t('For non-image documents such as PDFs, MS Office documents, or high-resolution images intended for download, consider using a third-party file sharing service instead of uploading to the Media Library. See <a href="@url" target="_blank">this KB article<span class="ut-cta-link--external"></span></a> for additional information.', ['@url' => 'https://ut.service-now.com/sp?id=kb_article&number=KB0019227']));
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_insert().
   */
  #[Hook('file_insert')]
  public function fileInsert(FileInterface $file) {
    return \Drupal::service('class_resolver')
      ->getInstanceFromDefinition(FileInsertHookHandler::class)
      ->process($file);
  }

}
