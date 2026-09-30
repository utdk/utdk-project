<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\file\Entity\File;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure settings for the utexas module.
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
    return 'utexas_general_config';
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
    $form['intro']['#markup'] = $this->t('<h3>Introduction</h3><p>The UT Drupal Kit is a website solution tailored to the Texas brand, based on the Drupal content management system. This configuration section includes settings that are specifically provided by the UT Drupal Kit, distinct from general Drupal settings.</p><p>Permissions associated with the Drupal Kit can be assigned to site roles via the <a href="/admin/config/content/utexas/permissions">Permissions configuration</a> tab.');
    // We allow static calls to services.
    // phpcs:ignore
    $fid = \Drupal::state()->get('default_og_image');
    if (!$fid) {
      $fid = 0;
    }
    $form['seo'] = [
      '#title' => 'Search Engine Optimization',
      '#type' => 'fieldset',
    ];
    $form['seo']['default_og_image'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://',
      '#multiple' => FALSE,
      '#description' => $this->t('Allowed extensions: jpg, jpeg, png. You must press "Save configuration" for changes to take effect.'),
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'jpg jpeg png'],
      ],
      '#default_value' => [$fid],
      '#title' => $this->t('Default image for social media sharing'),
    ];
    $full_html_default = \Drupal::state()->get('full_html_updates') ?? 0;
    $form['development_settings'] = [
      '#title' => 'Development settings',
      '#type' => 'fieldset',
    ];
    $form['development_settings']['full_html_updates'] = [
      '#type' => 'checkbox',
      '#title' => 'Receive configuration updates for the "Full HTML" text format',
      '#description' => $this->t('The "Full HTML" text format is a rich text editing configuration provided by the Drupal Kit. Periodically, the Drupal Kit updates configuration for this text format. For example, it may add a new option to the "Styles" dropdown or add a new text filter. Leave this checkbox selected to automatically receive those updates. For sites where developers have made their own customizations to the "Full HTML" text format, deselecting this checkbox provides a way to ensure that Drupal Kit updates to the text format do not overwrite those customizations.'),
      '#default_value' => $full_html_default,
    ];
    $display_links = \Drupal::state()->get('display_links') ?? 0;
    $form['toolbar_links'] = [
      '#title' => 'Drupal Kit Support Links',
      '#type' => 'fieldset',
    ];
    $form['toolbar_links']['display_links'] = [
      '#type' => 'checkbox',
      '#title' => 'Display Drupal Kit support links',
      '#description' => $this->t('Links to email Drupal Kit support, the Drupal Kit demo site, and Drupal Kit documentation will be displayed in the admin toolbar.'),
      '#default_value' => $display_links,
    ];
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // We allow static calls to services.
    // phpcs:ignore
    $config = \Drupal::configFactory();
    // phpcs:ignore
    $state_api = \Drupal::state();
    // Set Full HTML configuration opt-in.
    $state_api->set('full_html_updates', $form_state->getValue('full_html_updates'));
    // Toolbar links.
    $state_api->set('display_links', $form_state->getValue('display_links'));
    // Set default OG image.
    $metatag_default = $config->getEditable('metatag.metatag_defaults.global');
    $field = $form_state->getValue('default_og_image');
    if (!isset($field[0])) {
      // The OG image has been cleared. Reflect this in the settings.
      $state_api->delete('default_og_image');
      $tags = $metatag_default->get('tags');
      unset($tags['og_image']);
      $metatag_default->set('tags', $tags);
      $metatag_default->save();
    }
    else {
      // Upload the new default OG image.
      // We allow static calls to services.
      // phpcs:ignore
      $file = File::load($field[0]);
      // This will set the file status to 'permanent' automatically.
      // We allow static calls to services.
      // phpcs:ignore
      \Drupal::service('file.usage')->add($file, 'utexas', 'file', $file->id());
      $state_api->set('default_og_image', $file->id());
      $uri = $file->getFileUri();
      // We allow static calls to services.
      // phpcs:ignore
      $filepath = \Drupal::service('file_url_generator')->generateString($uri);
      $tags = $metatag_default->get('tags');
      $tags['og_image'] = $filepath;
      $metatag_default->set('tags', $tags);
      $metatag_default->save();
    }
    drupal_flush_all_caches();
  }

}
