<?php

namespace Drupal\utexas_instagram_api;

/**
 * Instagram classes to integrate with the Instagram API.
 */

/**
 * Main Instagram API request class.
 */
interface UTexasInstagramApiInterface {

  /**
   * The API endpoint.
   *
   * @var string
   */
  const INSTAGRAM_API_URI = 'https://instagram.com/';

  /**
   * The GRAPH endpoint.
   *
   * @var string
   */
  const INSTAGRAM_DATA_URI = 'https://graph.instagram.com/v21.0/';

  /**
   * The prefix for STATE API keys.
   *
   * @var string
   */
  const STATE_API_PREFIX = 'utexas_instagram_api.ig_auth.';

  /**
   * Constructs the request object.
   *
   * @param string $config_id
   *   The configuration object ID.
   */
  public function __construct($config_id);

  /**
   * Get access token.
   */
  public function getToken();

  /**
   * Set access token.
   *
   * @param string $token
   *   The token value.
   */
  public function setToken($token);

  /**
   * Get access token.
   */
  public function getTokenExpiration();

  /**
   * Set access token.
   *
   * @param string $expiration
   *   The expiration time as a timestamp value.
   */
  public function setTokenExpiration($expiration);

  /**
   * Get authorization code.
   */
  public function getShortToken();

  /**
   * Get OAuth short-lived authorization code.
   *
   * GET https://instagram.com/oauth/authorize
   *  ?client_id={instagram-app-id}
   *  &redirect_uri={redirect-uri}
   *  &scope={scope}
   *  &response_type=code
   *  &state={state}
   */
  public function getOauthAuthorizationCode();

  /**
   * Get a new OAuth short-lived access token.
   *
   * Curl -X POST \
   * https://instagram.com/oauth/access_token \
   * -F client_id={instagram-app-id} \
   * -F client_secret={instagram-app-secret} \
   * -F grant_type=authorization_code \
   * -F redirect_uri={redirect-uri} \
   * -F code=AQBx-hBsH3...
   */
  public function getOauthAccessToken();

  /**
   * Get a new long-lived Graph API access token using short-lived OAuth token.
   *
   * GET https://graph.instagram.com/access_token
   *  ?grant_type=ig_exchange_token
   *  &client_secret={instagram-app-secret}
   *  &access_token={short-lived-access-token}
   */
  public function getGraphAccessToken();

  /**
   * Get a new long-lived token using existing active long-lived token.
   *
   * GET https://graph.instagram.com/refresh_access_token
   *  ?grant_type=ig_refresh_token
   *  &access_token={long-lived-access-token}
   */
  public function refreshGraphAccessToken();

  /**
   * Get media.
   *
   * GET "https://graph.instagram.com/me/media
   * ?access_token={$access_token}
   * &fields=caption,id,media_type,media_url,permalink,thumbnail_url,timestamp,username
   * &limit=9
   *
   * caption - The Media's caption text. Not returnable for Media in albums.
   * id - The Media's ID.
   * media_type - The Media's type. Can be IMAGE, VIDEO, or CAROUSEL_ALBUM.
   * media_url - The Media's URL.
   * permalink - The Media's permanent URL. Will be omitted if the Media
   *  contains copyrighted material, or has been flagged for a copyright
   *  violation.
   * thumbnail_url - The Media's thumbnail image URL. Only available on VIDEO
   *  Media.
   * timestamp - The Media's publish date in ISO 8601 format.
   * username - The Media owner's username.
   */
  public function getMedia();

  /**
   * Get media.
   *
   * GET "https://graph.instagram.com/me
   * ?access_token={$access_token}
   * &fields=username.
   *
   * username - The Media owner's username.
   */
  public function getCurrentUserAccount();

}
