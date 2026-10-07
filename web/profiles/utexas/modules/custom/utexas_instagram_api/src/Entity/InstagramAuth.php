<?php

namespace Drupal\utexas_instagram_api\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utexas_instagram_api\InstagramAuthInterface;
use Drupal\utexas_instagram_api\UTexasInstagramApi;

/**
 * Defines the UTexas Instagram API ig_auth entity.
 */
#[ConfigEntityType(
  id: 'utexas_ig_auth',
  label: new TranslatableMarkup('Instagram account authorization'),
  handlers: [
    'list_builder' => 'Drupal\utexas_instagram_api\Controller\InstagramAuthListBuilder',
    'form' => [
      'add' => 'Drupal\utexas_instagram_api\Form\InstagramAuthForm',
      'edit' => 'Drupal\utexas_instagram_api\Form\InstagramAuthForm',
      'delete' => 'Drupal\utexas_instagram_api\Form\InstagramAuthDeleteForm',
    ],
  ],
  config_prefix: 'ig_auth',
  admin_permission: 'administer site configuration',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  config_export: [
    'id',
    'label',
    'client_id',
    'client_secret',
    'redirect_uri_override',
  ],
  links: [
    'edit-form' => '/admin/config/media/utexas-instagram-api/instagram-authorization/{utexas_ig_auth}',
    'delete-form' => '/admin/config/media/utexas-instagram-api/instagram-authorization/{utexas_ig_auth}/delete',
  ]
)]
class InstagramAuth extends ConfigEntityBase implements InstagramAuthInterface {

  /**
   * The utexas_ig_auth ID.
   *
   * @var string
   */
  public $id;

  /**
   * The utexas_ig_auth label.
   *
   * @var string
   */
  public $label;

  /**
   * The utexas_ig_auth access token.
   *
   * @var string
   */
  public $access_token;

  /**
   * The utexas_ig_auth client id.
   *
   * @var string
   */
  public $client_id;

  /**
   * The utexas_ig_auth client secret.
   *
   * @var string
   */
  public $client_secret;

  /**
   * {@inheritdoc}
   */
  public function getAccessToken() {
    $request = new UTexasInstagramApi($this->id());
    return $request->getToken();
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessTokenExpiration() {
    $request = new UTexasInstagramApi($this->id());
    return $request->getTokenExpiration();
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessToken($access_token) {
    $request = new UTexasInstagramApi($this->id());
    $request->setToken($access_token);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOauthAuthorizationCode() {
    $request = new UTexasInstagramApi($this->id());
    return $request->getOauthAuthorizationCode();
  }

  /**
   * {@inheritdoc}
   */
  public function getClientId() {
    return $this->get('client_id');
  }

  /**
   * {@inheritdoc}
   */
  public function setClientId($client_id) {
    $this->set('client_id', $client_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectUriOverride() {
    return $this->get('redirect_uri_override');
  }

  /**
   * {@inheritdoc}
   */
  public function setRedirectUriOverride($redirect_uri_override) {
    $this->set('redirect_uri_override', $redirect_uri_override);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getClientSecret() {
    return $this->get('client_secret');
  }

  /**
   * {@inheritdoc}
   */
  public function setClientSecret($client_secret) {
    $this->set('client_secret', $client_secret);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    // Note that retrieved token is returned to a route with this method.
    $this->getOauthAuthorizationCode();
  }

  /**
   * Instagram authorization entities to use as select list options.
   *
   * @param bool $include_empty
   *   If TRUE a '- None -' option will be inserted in the options array.
   *
   * @return array
   *   Array of image styles both key and value are set to style name.
   */
  public static function authOptions($include_empty = TRUE) {
    $auths = self::loadMultiple();
    $options = [];
    if ($include_empty && !empty($auths)) {
      $options[''] = t('- None -');
    }
    foreach ($auths as $name => $auth) {
      $options[$name] = $auth->label();
    }

    if (empty($options)) {
      $options[''] = t('No defined authorizations');
    }
    return $options;
  }

}
