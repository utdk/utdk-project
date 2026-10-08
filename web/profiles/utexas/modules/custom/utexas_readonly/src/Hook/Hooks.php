<?php

namespace Drupal\utexas_readonly\Hook;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\utexas_readonly\ReadOnlyHelper;

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
    if ($form_id == 'image_style_flush_form') {
      return;
    }
    // The taxonomy overview page should not be restricted so terms can be
    // reordered (#3328).
    if ($form_id === 'taxonomy_overview_terms') {
      return;
    }
    $form_entity = $form_object->getEntity();
    $form_id = $form_entity->id() ?? '';
    $parts = explode('.', $form_id);
    $base_entity = $parts[1] ?? $form_id;
    $restricted_entities = array_merge(ReadOnlyHelper::$restrictedBlockTypes, ReadOnlyHelper::$restrictedMediaTypes, ReadOnlyHelper::$restrictedNodeTypes, ReadOnlyHelper::$restrictedConfig, ReadOnlyHelper::$restrictedFields);
    $starts_with_restricted_pattern = FALSE;
    foreach (ReadOnlyHelper::$restrictedPatterns as $pattern) {
      // 'Starts with..."
      if (strpos($base_entity, $pattern) === 0) {
        $starts_with_restricted_pattern = TRUE;
      }
    }
    if ($starts_with_restricted_pattern || in_array($base_entity, $restricted_entities, TRUE)) {
      ReadOnlyHelper::warn();
      // Disable various form elements.
      $form['#validate'][] = [$this, 'validateFailure'];
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
  }

  /**
   * Helper validation function that always returns false.
   *
   * @param array $form
   *   A build form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function validateFailure(array $form, FormStateInterface &$form_state) {
    $form_state->setErrorByName(NULL, $this->t('This component is read-only and should not be modified.'));
  }

  /**
   * Implements hook_entity_operation_alter().
   */
  #[Hook('entity_operation_alter')]
  public function entityOperationAlter(array &$operations, EntityInterface $entity) {
    $entity_type = $entity->getEntityTypeId();
    $restricted_entities = array_merge(ReadOnlyHelper::$restrictedBlockTypes, ReadOnlyHelper::$restrictedMediaTypes, ReadOnlyHelper::$restrictedNodeTypes, ReadOnlyHelper::$restrictedConfig);
    $parts = explode('.', $entity->id());
    $base_entity = $parts[1] ?? $entity->id();
    if (!in_array($base_entity, $restricted_entities)) {
      return;
    }
    switch ($entity_type) {
      case 'field_config':
        $operations = [
          'locked' => [
            'title' => $this->t('Read-only'),
          ],
        ];
        break;

      case 'view':
        // Partially lock Views, but still leave disabling.
        unset($operations['edit']);
        unset($operations['duplicate']);
        unset($operations['delete']);
        break;

      case 'filter_format':
        // Label text form entity operations as read-only.
        foreach ($operations as $key => $value) {
          if (!in_array($key, ['edit'])) {
            unset($operations[$key]);
          }
        }
        $operations['edit']['title'] = $this->t('View configuration (read-only)');
        break;

      case 'node_type':
      case 'block_content_type':
      case 'media_type':
        // Label node/block entity operations as read-only.
        foreach ($operations as $key => $value) {
          if (!in_array($key, ['manage-fields'])) {
            unset($operations[$key]);
          }
        }
        $operations['manage-fields']['title'] = $this->t('View configuration (read-only)');
        break;
    }
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments) {
    // Add display modifications to specific pages that cannot be
    // targeted by route.
    $read_only_paths = [];
    foreach (ReadOnlyHelper::$restrictedNodeTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/display';
    }
    foreach (ReadOnlyHelper::$restrictedMediaTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/display';
    }
    foreach (ReadOnlyHelper::$restrictedBlockTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/block/block-content/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/block/block-content/manage/' . $machine_name . '/fields/add-field';
    }
    $current_path = \Drupal::service('path.current')->getPath();
    if (in_array($current_path, $read_only_paths)) {
      $attachments['#attached']['library'][] = 'utexas_readonly/base';
      ReadOnlyHelper::warn();
    }
  }

}
