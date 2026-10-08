<?php

namespace Drupal\utexas_form_elements\Plugin\views\area;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\Attribute\ViewsArea;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an area plugin to display a link to set the site homepage.
 *
 * @ingroup views_area_handlers
 */
#[ViewsArea("set_site_homepage")]
class SetSiteHomepage extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $account = \Drupal::currentUser();
    if (!$empty || !empty($this->options['empty'])) {
      $link_options = [
        'attributes' => [
          'class' => [
            'ut-btn--secondary',
            'ut-cta-link--angle-right',
          ],
        ],
      ];
      $url = Url::fromRoute('system.site_information_settings');
      $url->setOptions($link_options);
      $link = Link::fromTextAndUrl($this->t('Set the site homepage'), $url)->toString();
      $element = [
        '#markup' => '<p>' . $link . '</p>',
        '#access' =>
        $account->hasPermission('administer site configuration'),
      ];
      return $element;
    }
    return [];
  }

}
