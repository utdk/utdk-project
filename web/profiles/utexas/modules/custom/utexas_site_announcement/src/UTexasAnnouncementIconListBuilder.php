<?php

namespace Drupal\utexas_site_announcement;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a listing of UTexas Announcement Icon entities.
 */
class UTexasAnnouncementIconListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['icon'] = $this->t('Icon');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $file = $entity->get('icon');
    $icon_markup = "Missing Image";
    if (file_exists($file) && $icon = file_get_contents($file)) {
      $icon_markup = Markup::create($icon);
    }
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['icon'] = $icon_markup;
    return $row + parent::buildRow($entity);
  }

}
