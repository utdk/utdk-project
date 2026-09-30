<?php

namespace Drupal\utexas_instagram_api;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an Example entity.
 */
interface InstagramAuthInterface extends ConfigEntityInterface {

  /**
   * Returns the access token.
   *
   * @return string
   *   The text of the access token.
   */
  public function getAccessToken();

  /**
   * Returns the access token expiration timestamp string.
   *
   * @return string
   *   The text of the access token expiration.
   */
  public function getAccessTokenExpiration();

  /**
   * Sets the text of the access token.
   *
   * @param string $access_token
   *   The text of the access token.
   *
   * @return $this
   *   The class instance this method is called on.
   */
  public function setAccessToken($access_token);

  /**
   * Returns the access token.
   *
   * @return string
   *   The text of the access token.
   */
  public function getRedirectUriOverride();

  /**
   * Sets the text of the redirect URI override.
   *
   * @param string $redirect_uri_override
   *   The text of the redirect URI.
   *
   * @return $this
   *   The class instance this method is called on.
   */
  public function setRedirectUriOverride($redirect_uri_override);

  /**
   * Returns the access token.
   *
   * @return string
   *   The text of the access token.
   */
  public function getClientId();

  /**
   * Sets the text of the client_id.
   *
   * @param string $client_id
   *   The text of the client_id.
   *
   * @return $this
   *   The class instance this method is called on.
   */
  public function setClientId($client_id);

  /**
   * Returns the client secret.
   *
   * @return string
   *   The text of the client secret.
   */
  public function getClientSecret();

  /**
   * Sets the text of the client secret.
   *
   * @param string $client_secret
   *   The text of the access token.
   *
   * @return $this
   *   The class instance this method is called on.
   */
  public function setClientSecret($client_secret);

}
