<?php

namespace Drupal\utexas_site_announcement\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the UTexas Announcement Color Scheme entity.
 */
#[ConfigEntityType(
  id: 'utexas_announcement_color_scheme',
  label: new TranslatableMarkup('Announcement Color Scheme'),
  handlers: [
    'view_builder' => 'Drupal\Core\Entity\EntityViewBuilder',
    'list_builder' => 'Drupal\utexas_site_announcement\UTexasAnnouncementColorSchemeListBuilder',
    'form' => [
      'add' => 'Drupal\utexas_site_announcement\Form\UTexasAnnouncementColorSchemeForm',
      'edit' => 'Drupal\utexas_site_announcement\Form\UTexasAnnouncementColorSchemeForm',
      'delete' => 'Drupal\utexas_site_announcement\Form\UTexasAnnouncementColorSchemeDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\utexas_site_announcement\UTexasAnnouncementColorSchemeHtmlRouteProvider',
    ],
  ],
  config_prefix: 'utexas_announcement_color_scheme',
  config_export: [
    'id',
    'label',
    'background_color',
    'text_color',
    'uuid',
  ],
  admin_permission: 'administer utexas announcement color schemes',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'background_color' => 'background_color',
    'text_color' => 'text_color',
    'uuid' => 'uuid',
  ],
  links: [
    'add-form' => '/admin/config/site-announcement/color-scheme/add',
    'edit-form' => '/admin/config/site-announcement/color-scheme/{utexas_announcement_color_scheme}/edit',
    'delete-form' => '/admin/config/site-announcement/color-scheme/{utexas_announcement_color_scheme}/delete',
    'collection' => '/admin/config/site-announcement/color-scheme',
  ]
)]
class UTexasAnnouncementColorScheme extends ConfigEntityBase {

  /**
   * The announcement color scheme ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The announcement color scheme label.
   *
   * @var string
   */
  protected $label;

  /**
   * The announcement color scheme background color.
   *
   * @var string
   */
  protected $background_color;

  /**
   * The announcement color scheme text color.
   *
   * @var string
   */
  protected $text_color;

  /**
   * Helper function to get current background color.
   */
  public function getBackgroundColor() {
    return $this->background_color ?? '';
  }

  /**
   * Helper function to get current text color.
   */
  public function getTextColor() {
    return $this->text_color ?? '';
  }

}
