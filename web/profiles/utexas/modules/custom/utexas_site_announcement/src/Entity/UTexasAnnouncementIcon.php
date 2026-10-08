<?php

namespace Drupal\utexas_site_announcement\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the UTexas Announcement Icon entity.
 */
#[ConfigEntityType(
  id: 'utexas_announcement_icon',
  label: new TranslatableMarkup('Announcement Icon'),
  handlers: [
    'view_builder' => 'Drupal\Core\Entity\EntityViewBuilder',
    'list_builder' => 'Drupal\utexas_site_announcement\UTexasAnnouncementIconListBuilder',
    'form' => [
      'add' => 'Drupal\utexas_site_announcement\Form\UTexasAnnouncementIconForm',
      'edit' => 'Drupal\utexas_site_announcement\Form\UTexasAnnouncementIconForm',
      'delete' => 'Drupal\utexas_site_announcement\Form\UTexasAnnouncementIconDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\utexas_site_announcement\UTexasAnnouncementIconHtmlRouteProvider',
    ],
  ],
  config_prefix: 'utexas_announcement_icon',
  config_export: [
    'id',
    'label',
    'icon',
    'uuid',
  ],
  admin_permission: 'administer utexas announcement icons',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'icon' => 'icon',
    'uuid' => 'uuid',
  ],
  links: [
    'add-form' => '/admin/config/site-announcement/icons/add',
    'edit-form' => '/admin/config/site-announcement/icons/{utexas_announcement_icon}/edit',
    'delete-form' => '/admin/config/site-announcement/icons/{utexas_announcement_icon}/delete',
    'collection' => '/admin/config/site-announcement/icons',
  ]
)]
class UTexasAnnouncementIcon extends ConfigEntityBase {

  /**
   * The announcement icon ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The announcement icon label.
   *
   * @var string
   */
  protected $label;

  /**
   * The announcement icon icon.
   *
   * @var string
   */
  protected $icon;

}
