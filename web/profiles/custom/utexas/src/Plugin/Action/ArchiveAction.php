<?php

namespace Drupal\utexas\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Action\Plugin\Action\Derivative\EntityPublishedActionDeriver;
use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Archives an entity that uses the UTexas-provided 'standard_workflow'.
 */
#[Action(
  id: 'entity:utexas_archive_action',
  action_label: new TranslatableMarkup('Workflow: Archive content'),
  deriver: EntityPublishedActionDeriver::class
)]
class ArchiveAction extends EntityActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(?EntityInterface $entity = NULL) {
    $entity->set('moderation_state', 'archived');
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\content_moderation\ModerationInformationInterface $moderation_info */
    $moderation_info = \Drupal::service('content_moderation.moderation_information');

    // First check whether the user has the permission to archive.
    /** @var \Drupal\Core\Entity\EntityInterface $object */
    $result = AccessResult::allowedIfHasPermission($account, 'use standard_workflow transition archive');

    // Additionally check if the entity is eligible for moderation workflow.
    if (!$moderation_info->isModeratedEntity($object)) {
      return $result->isForbidden();
    }
    if ($moderation_info->getWorkflowForEntity($object)->id() !== 'standard_workflow') {
      return $result->isForbidden();
    }
    return $return_as_object ? $result : $result->isAllowed();
  }

}
