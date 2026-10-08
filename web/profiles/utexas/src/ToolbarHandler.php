<?php

namespace Drupal\utexas;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Toolbar integration handler.
 */
class ToolbarHandler implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * ToolbarHandler constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The HTTP request.
   */
  public function __construct(AccountProxyInterface $account, RequestStack $request_stack) {
    $this->account = $account;
    $this->requestStack = $request_stack;
  }

  /**
   * The Request handler.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('request_stack')
    );
  }

  /**
   * Hook bridge.
   *
   * @return array
   *   The toolbar items render array.
   *
   * @see hook_toolbar()
   */
  public function toolbar() {

    // Show Drupal Kit support links.
    if (!\Drupal::state()->get('display_links')) {
      return [];
    }

    $items['utdk_support'] = [
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];

    $options = [
      'query' => [
        'subject' => 'Drupal Kit support request: ' . $this->requestStack->getCurrentRequest()->getHost(),
      ],
    ];

    if ($this->account->hasRole('utexas_site_manager') || $this->account->hasRole('utexas_content_editor')) {
      $items['support'] = [
        '#type' => 'toolbar_item',
        'tab' => [
          '#type' => 'link',
          '#title' => $this->t('Open support ticket'),
          '#url' => Url::fromUri('mailto:drupal-kit-support@utlists.utexas.edu', $options),
          '#attributes' => [
            'title' => $this->t('Drupal Kit support'),
            'class' => ['toolbar-icon', 'toolbar-icon-entity-user-collection'],
          ],
        ],
        '#weight' => 998,
      ];
      $items['docs'] = [
        '#type' => 'toolbar_item',
        'tab' => [
          '#type' => 'link',
          '#title' => $this->t('Drupal Kit documentation'),
          '#url' => Url::fromUri('https://utexas.sharepoint.com/sites/UTDK'),
          '#attributes' => [
            'title' => $this->t('Drupal Kit documentation'),
            'class' => ['toolbar-icon', 'toolbar-icon-system-admin-content'],
            'target' => '_blank',
          ],
        ],
        '#weight' => 999,
      ];
      $items['demo'] = [
        '#type' => 'toolbar_item',
        'tab' => [
          '#type' => 'link',
          '#title' => $this->t('Drupal Kit demo site'),
          '#url' => Url::fromUri('https://demo.drupalkit.its.utexas.edu'),
          '#attributes' => [
            'title' => $this->t('Drupal Kit demo site'),
            'class' => ['toolbar-icon', 'toolbar-icon-system-admin-content'],
            'target' => '_blank',
          ],
        ],
        '#weight' => 1000,
      ];
    }

    return $items;
  }

}
