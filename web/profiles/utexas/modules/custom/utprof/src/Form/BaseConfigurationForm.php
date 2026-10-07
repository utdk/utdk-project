<?php

namespace Drupal\utprof\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure settings for the utprof module.
 */
class BaseConfigurationForm extends ConfigFormBase {

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   The typed config manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager, MessengerInterface $messenger, TypedConfigManagerInterface $typed_config_manager) {
    parent::__construct($config_factory, $typed_config_manager);
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('config.typed'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utprof_general_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro']['#markup'] = $this->t('<h3>Introduction</h3><p>The Profile feature provides the ability to create persona-based content in flexible, templated displays. The most common uses for the Profile include faculty and staff directories, endowed positions, donor lists, and featured students. Full documentation can be found at <a href="https://utexas.sharepoint.com/sites/UTDK/SitePages/profiles.aspx">https://utexas.sharepoint.com/sites/UTDK/SitePages/profiles.aspx</a>.</p><p>This feature does not currently have any display settings. Permissions associated with this feature can be assigned to site roles via the <a href="/admin/config/content/utprof/permissions">Permissions configuration</a> tab.');

    // Remove the default submit button provided by ConfigFormBase
    // until we have actual settings on this page by NOT returning the parent:
    // parent::buildForm($form, $form_state);.
    return $form;
  }

}
