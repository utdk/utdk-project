<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\utexas\Permissions;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure settings for the utexas module.
 */
class PermissionsConfigurationForm extends ConfigFormBase {

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
    return 'utexas_permissions_config';
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
    $allowed_roles = Permissions::getAllowedRoles();
    $options['Anonymous user'] = [];
    $options['Authenticated user'] = [];
    foreach ($allowed_roles as $rid => $role) {
      $options[$rid] = $role->label();
    }

    $form['intro']['#markup'] = $this->t('<h3>Assigning permissions</h3><p>Use this form to assign permissions associated with the Drupal Kit to user role(s) within the site. Notes:</p> <ol><li>First review existing permissions on the role(s) you intend to modify by visiting the site\'s <a href="/admin/people/permissions">permissions overview page</a> (not all accounts have access).</li><li>Only already <em>existing roles</em> in the system may have permissions assigned through this form. The <em>anonymous</em> and <em>authenticated</em> roles are disabled as a security precaution.</li>
    <li>These permissions will be <em>added to the existing permissions</em> on the selected role(s). No permissions will be removed.</li></ol></p>');

    $form['assign_manager_permissions'] = [
      '#type' => 'select',
      '#title' => $this->t('Assign management permissions'),
      '#description' => $this->t('Select a role below and press "Add permission to selected role(s)" to add management-related permissions to the specified role.'),
      '#empty_option' => '- Select -',
      '#options' => $options,
    ];
    $form['management_show'] = [
      '#type' => 'details',
      '#title' => $this->t('Management-related permissions that will be added'),
      '#collapsible' => TRUE,
      '#open' => FALSE,
    ];
    $form['management_show']['table'] = [
      '#type' => 'table',
      '#header' => ['Title', 'Machine name'],
      '#rows' => Permissions::displayPermissions(Permissions::$manager),
    ];

    $form['assign_editor_permissions'] = [
      '#type' => 'select',
      '#title' => $this->t('Assign content editing permissions'),
      '#description' => $this->t('Select a role below and press "Add permission to selected role(s)" to add content editing-related permissions to the specified role.'),
      '#empty_option' => '- Select -',
      '#options' => $options,
    ];
    $form['content_show'] = [
      '#type' => 'details',
      '#title' => $this->t('Content editing-related permissions that will be added'),
      '#collapsible' => TRUE,
      '#open' => FALSE,
    ];

    $form['content_show']['table'] = [
      '#type' => 'table',
      '#header' => ['Title', 'Machine name'],
      '#rows' => Permissions::displayPermissions(Permissions::$editor),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Add permissions to selected role(s)'),
      '#button_type' => 'primary',
    ];

    // By default, render the form using system-config-form.html.twig.
    $form['#theme'] = 'system_config_form';
    return $form;
  }

  /**
   * Extended submit callback.
   */
  public function submitForm(&$form, FormStateInterface $form_state) {
    $allowed_roles = array_keys(Permissions::getAllowedRoles());

    $manager_role = $form_state->getValue('assign_manager_permissions');
    if (in_array($manager_role, $allowed_roles)) {
      if (Permissions::assignPermissions('manager', $manager_role)) {
        $this->messenger->addMessage($this->t('Management permissions have been added to the %role role.', ['%role' => $manager_role]));
      }
    }

    $editor_role = $form_state->getValue('assign_editor_permissions');
    if (in_array($editor_role, $allowed_roles)) {
      if (Permissions::assignPermissions('editor', $editor_role)) {
        $this->messenger->addMessage($this->t('Content editing permissions have been added to the %role role.', ['%role' => $editor_role]));
      }
    }
  }

}
