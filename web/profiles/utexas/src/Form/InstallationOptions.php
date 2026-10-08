<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for selecting which UTexas extensions to install.
 */
class InstallationOptions extends FormBase {

  /**
   * The state helper.
   *
   * @var \Drupal\Core\Extension\ModuleInstaller
   */
  protected $stateFactory;

  /**
   * ExtensionSelectForm constructor.
   *
   * @param \Drupal\Core\State\State $stateFactory
   *   The module state service.
   */

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructor.
   */
  public function __construct(State $stateFactory, ConfigFactory $config_factory) {
    $this->stateFactory = $stateFactory;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utexas_installation_options';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array &$install_state = NULL) {
    $form['#title'] = $this->t('Installation options');
    $form['install_news'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable <a href=":url" target="_blank">News feature<span class="ut-cta-link--external"></span></a>', [
        ':url' => 'https://utexas.sharepoint.com/sites/UTDK/SitePages/news.aspx',
      ]),
      '#default_value' => 0,
      '#weight' => 1,
    ];
    $form['install_event'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable <a href=":url" target="_blank">Event feature<span class="ut-cta-link--external"></span></a>', [
        ':url' => 'https://utexas.sharepoint.com/sites/UTDK/SitePages/events.aspx',
      ]),
      '#default_value' => 0,
      '#weight' => 2,
    ];
    $form['install_profile'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable <a href=":url" target="_blank">Profile feature<span class="ut-cta-link--external"></span></a>', [
        ':url' => 'https://utexas.sharepoint.com/sites/UTDK/SitePages/profiles.aspx',
      ]),
      '#default_value' => 0,
      '#weight' => 3,
    ];
    $form['install_scheduled_transitions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable <a href=":url" target="_blank">Scheduled Transitions feature<span class="ut-cta-link--external"></span></a>', [
        ':url' => 'https://utexas.sharepoint.com/sites/UTDK/SitePages/scheduled-transitions.aspx',
      ]),
      '#default_value' => 0,
      '#weight' => 4,
    ];
    $form['default_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Populate example pages and menu items to model realistic site content.'),
      '#default_value' => 1,
      '#weight' => 5,
    ];
    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Complete installation'),
      ],
      '#type' => 'actions',
      '#weight' => 5,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($values['default_content']) {
      $this->stateFactory->set('utexas_installation_options.default_content', TRUE);
    }
    if ($values['install_news']) {
      $this->stateFactory->set('utexas_installation_options.install_news', TRUE);
    }
    if ($values['install_event']) {
      $this->stateFactory->set('utexas_installation_options.install_event', TRUE);
    }
    if ($values['install_profile']) {
      $this->stateFactory->set('utexas_installation_options.install_profile', TRUE);
    }
    if ($values['install_scheduled_transitions']) {
      $this->stateFactory->set('utexas_installation_options.install_scheduled_transitions', TRUE);
    }
  }

}
