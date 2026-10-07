<?php

namespace Drupal\utexas_site_announcement;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of UTexas Announcement Color Scheme entities.
 */
class UTexasAnnouncementColorSchemeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['background_color'] = $this->t('Background Color');
    $header['text_color'] = $this->t('Text Color');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['background_color'] = $entity->getBackgroundColor();
    $row['text_color'] = $entity->getTextColor();
    return $row + parent::buildRow($entity);
  }

}
