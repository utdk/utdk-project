<?php

namespace Drupal\utexas_google_search\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form builder for the searchbox forms.
 *
 * @package Drupal\utexas_google_search\Form
 */
class UtexasGoogleSearchForm extends FormBase {

  /**
   * RequestStack object for getting requests.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * UtexasGoogleSearchForm constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(RequestStack $requestStack, ConfigFactoryInterface $config_factory, RendererInterface $renderer) {
    $this->requestStack = $requestStack;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utexas_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $google_pse_id = \Drupal::state()->get('utexas.google_pse_id') ?? '';
    if (empty($google_pse_id)) {
      return [];
    }
    // We allow static calls to Drupal methods.
    // phpcs:ignore
    $user_input = \Drupal::request()->query->get('keys') ?? '';
    $form['#action'] = Url::fromRoute('utexas_google_search.search')->toString();
    $form['keys'] = [
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
      '#id' => 'google-cse-query',
      '#size' => 40,
      '#default_value' => $user_input,
      '#placeholder' => 'Search the site...',
      '#attributes' => ['title' => $this->t('Enter the terms you wish to search for.')],
    ];
    $form['#cache']['contexts'][] = 'url.query_args:keys';
    $form['actions'] = ['#type' => 'actions'];
    $form['#method'] = 'get';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      // Prevent op from showing up in the query string.
      '#name' => '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This form submits to the search page, so processing happens there.
  }

}
