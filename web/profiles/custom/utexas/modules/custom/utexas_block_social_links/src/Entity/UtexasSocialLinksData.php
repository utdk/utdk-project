<?php

namespace Drupal\utexas_block_social_links\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the UTexas Block Social Links entity.
 */
#[ConfigEntityType(
  id: 'utexas_social_links_data',
  label: new TranslatableMarkup('Social Links'),
  handlers: [
    'view_builder' => 'Drupal\Core\Entity\EntityViewBuilder',
    'list_builder' => 'Drupal\utexas_block_social_links\UtexasSocialLinksDataListBuilder',
    'form' => [
      'add' => 'Drupal\utexas_block_social_links\Form\UtexasSocialLinksDataForm',
      'edit' => 'Drupal\utexas_block_social_links\Form\UtexasSocialLinksDataForm',
      'delete' => 'Drupal\utexas_block_social_links\Form\UtexasSocialLinksDataDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\utexas_block_social_links\UtexasSocialLinksDataHtmlRouteProvider',
    ],
  ],
  config_prefix: 'utexas_social_links_data',
  config_export: [
    'id',
    'label',
    'icon',
    'uuid',
  ],
  admin_permission: 'administer social links data config',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'icon' => 'icon',
    'uuid' => 'uuid',
  ],
  links: [
    'add-form' => '/admin/structure/social-links/add',
    'edit-form' => '/admin/structure/social-links/{utexas_social_links_data}/edit',
    'delete-form' => '/admin/structure/social-links/{utexas_social_links_data}/delete',
    'collection' => '/admin/structure/social-links',
  ]
)]
class UtexasSocialLinksData extends ConfigEntityBase {

  /**
   * The UTexas Block Social Links ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The UTexas Block Social Links label.
   *
   * @var string
   */
  protected $label;

  /**
   * The UTexas Block Social Links icon.
   *
   * @var string
   */
  protected $icon;

}
