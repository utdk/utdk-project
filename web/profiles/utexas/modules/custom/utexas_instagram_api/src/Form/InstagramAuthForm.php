<?php

namespace Drupal\utexas_instagram_api\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the Example add and edit forms.
 */
class InstagramAuthForm extends EntityForm {

  /**
   * Constructs an ExampleForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\utexas_instagram_api\InstagramAuthInterface $ig_auth */
    $ig_auth = $this->entity;
    // "Default" form items.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $ig_auth->label(),
      '#description' => $this->t("Label for the integration. This will appear as an option in dropdowns in the Instagram block."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $ig_auth->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$ig_auth->isNew(),
    ];

    // Field group 1.
    $form['group1'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Instagram App Information'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['group1']['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Instagram App ID"),
      '#maxlength' => 255,
      '#default_value' => $ig_auth->getClientId(),
      '#description' => $this->t("This value is located in https://developers.facebook.com/apps/YOUR-APP-ID/instagram-basic-display/basic-display/"),
      '#required' => TRUE,
    ];
    $form['group1']['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Instagram App Secret"),
      '#maxlength' => 255,
      '#default_value' => $ig_auth->getClientSecret(),
      '#description' => $this->t("This value is located in https://developers.facebook.com/apps/YOUR-APP-ID/instagram-basic-display/basic-display."),
      '#required' => TRUE,
    ];

    // Field group 2.
    $form['group2'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Developer Information'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $default_redirect = \Drupal::request()->getSchemeAndHttpHost() . '/admin/config/media/utexas-instagram-api/instagram-authorization/';
    $form['group2']['redirect_uri_override'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Redirect URI"),
      '#maxlength' => 255,
      '#default_value' => $ig_auth->getRedirectUriOverride() ?? $default_redirect,
      '#description' => $this->t("On https://developers.facebook.com/apps/YOUR-APP-ID/instagram-basic-display/basic-display/, the redirect URI *must* be set to %default_redirect (including trailing slash).", ['%default_redirect' => $default_redirect]),
    ];
    $form['group2']['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Access Token"),
      '#default_value' => $ig_auth->getAccessToken(),
      '#description' => $this->t("Instagram Access Token"),
      '#attributes' => [
        'disabled' => TRUE,
      ],
    ];
    $date = $ig_auth->getAccessTokenExpiration() ? date('m/d/Y H:i:s', $ig_auth->getAccessTokenExpiration()) : '';
    $form['group2']['expiration_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Token Expiration Date"),
      '#default_value' => $date,
      '#description' => $this->t("Instagram Access Token Expiration Date"),
      '#attributes' => [
        'disabled' => TRUE,
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $account = $this->entity;
    $status = $account->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label Instagram account was created.', [
        '%label' => $account->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Instagram account updated.', [
        '%label' => $account->label(),
      ]));
    }

    $form_state->setRedirect('entity.utexas_ig_auth.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $account = $this->entity;
    if (empty($account->id())) {
      $submit_button_message = $this->t('Create new authorization');
    }
    else {
      $submit_button_message = $this->t('Update authorization');
    }

    $actions['submit']['#value'] = $submit_button_message;

    return $actions;
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('utexas_ig_auth')->getQuery()
      ->accessCheck(FALSE)
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
