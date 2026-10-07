<?php

namespace Drupal\utevent_readonly\Hook;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\utevent_readonly\ReadOnlyHelper;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface &$form_state, $form_id) {
    if (!$form_object = $form_state->getFormObject()) {
      return;
    }
    if (!$form_object instanceof EntityFormInterface) {
      return;
    }
    $form_entity = $form_object->getEntity();
    // Only modify forms that match a predefined pattern.
    $form_entity_id = $form_entity->id() ?? '';
    if (!ReadOnlyHelper::matchesReadOnlyPattern($form_entity_id)) {
      return;
    }
    // The taxonomy overview page should not be restricted so terms can be
    // reordered (#3328).
    if ($form_id === 'taxonomy_overview_terms') {
      return;
    }
    ReadOnlyHelper::warn();
    // Disable various form elements.
    $form['#validate'][] = [static::class, 'validateFailure'];
    if (isset($form['actions']['submit'])) {
      $form['actions']['submit']['#disabled'] = TRUE;
    }
    if (isset($form['actions']['delete'])) {
      unset($form['actions']['delete']);
    }
    foreach (ReadOnlyHelper::$disabledFields as $field) {
      if (isset($form[$field])) {
        $form[$field]['#disabled'] = TRUE;
      }
      if (isset($form['submission'][$field])) {
        $form['submission'][$field]['#disabled'] = TRUE;
      }
      if (isset($form['cardinality_container'][$field])) {
        $form['cardinality_container'][$field]['#disabled'] = TRUE;
      }
    }
  }

  /**
   * Blocks submission for read-only forms.
   */
  public static function validateFailure(array &$form, FormStateInterface $form_state) {
    $form_state->setErrorByName('', \Drupal::translation()->translate('The Event feature is read-only and may not be changed.'));
  }

  /**
   * Implements hook_entity_operation_alter().
   */
  #[Hook('entity_operation_alter')]
  public function entityOperationAlter(array &$operations, EntityInterface $entity) {
    $entity_type = $entity->getEntityTypeId();
    if (!ReadOnlyHelper::matchesReadOnlyPattern($entity->id())) {
      return;
    }
    switch ($entity_type) {
      case 'field_config':
        $operations = [
          'locked' => [
            'title' => $this->t('Locked'),
          ],
        ];
        break;

      case 'taxonomy_vocabulary':
        // Partially lock Taxonomy, but still leave listing and adding terms.
        foreach ($operations as $key => $value) {
          if ($key != 'list' && $key != 'add') {
            unset($operations[$key]);
          }
        }
        break;

      case 'view':
        // Partially lock Views, but still leave disabling.
        unset($operations['edit']);
        unset($operations['duplicate']);
        unset($operations['delete']);
        break;

      case 'node_type':
      case 'block_content_type':
        // Label node/block entity operations as read-only.
        foreach ($operations as $key => $value) {
          if ($key !== 'manage-fields') {
            unset($operations[$key]);
          }
        }
        $operations['manage-fields']['title'] = $this->t('View fields (read-only)');
        break;
    }
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments) {
    // Add display modifications to specific pages.
    $restricted_paths = [
      '/admin/structure/types/manage/utevent_event/fields',
      '/admin/structure/types/manage/utevent_event/form-display',
      '/admin/structure/types/manage/utevent_event/display',
      '/admin/structure/block/block-content/manage/utevent_event_listing/fields',
      '/admin/structure/block/block-content/manage/utevent_event_listing/form-display',
      '/admin/structure/block/block-content/manage/utevent_event_listing/display',
      '/admin/structure/taxonomy/manage/utevent_location/overview/fields',
      '/admin/structure/taxonomy/manage/utevent_location/overview/form-display',
      '/admin/structure/taxonomy/manage/utevent_location/overview/display',
      '/admin/structure/taxonomy/manage/utevent_tags/overview/fields',
      '/admin/structure/taxonomy/manage/utevent_tags/overview/form-display',
      '/admin/structure/taxonomy/manage/utevent_tags/overview/display',
    ];
    $current_path = \Drupal::service('path.current')->getPath();
    if (in_array($current_path, $restricted_paths)) {
      $attachments['#attached']['library'][] = 'utevent_readonly/base';
    }
  }

}
