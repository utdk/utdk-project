<?php

namespace Drupal\utprof_block_type_profile_listing\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Attribute\EntityReferenceSelection;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides specific content type control for the entity_view_mode entity type.
 */
#[EntityReferenceSelection(
  id: "default:entity_view_mode",
  label: new TranslatableMarkup("Entity reference selection"),
  entity_types: ["entity_view_mode"],
  group: "default",
  weight: 1
)]
class ViewModeSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    // Add needed array key for entity type to the options array.
    $target_entity_type_id = 'entity_view_mode';
    $options[$target_entity_type_id] = $this->getAllowedViewModes();

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function getAllowedViewModes() {
    // Get enabled view modes by node bundle.
    /** @var \Drupal\Core\Entity\EntityDisplayRepository $entity_repository */
    $entity_repository = \Drupal::service('entity_display.repository');
    $view_mode_options = [];
    $view_mode_entity_type_id = 'node';
    $view_mode_bundle = 'utprof_profile';
    $view_mode_options = $entity_repository->getViewModeOptionsByBundle($view_mode_entity_type_id, $view_mode_bundle);

    // Unset unwanted view_mode_options.
    unset($view_mode_options['default']);
    unset($view_mode_options['full']);
    unset($view_mode_options['teaser']);

    // Prefix view_mode_options array keys with the entity_type_id.
    foreach ($view_mode_options as $key => $value) {
      $view_mode_options[$view_mode_entity_type_id . '.' . $key] = $value;
      unset($view_mode_options[$key]);
    }
    \Drupal::moduleHandler()->invokeAll('utprof_block_type_profile_listing_alter_view_modes', [&$view_mode_options]);
    return $view_mode_options;
  }

}
