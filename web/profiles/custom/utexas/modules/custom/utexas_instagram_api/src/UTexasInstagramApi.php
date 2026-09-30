<?php

namespace Drupal\utexas_instagram_api;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use GuzzleHttp\RequestOptions;

/**
 * Main Instagram API request class.
 */
class UTexasInstagramApi implements UTexasInstagramApiInterface {

  /**
   * An http response code.
   *
   * @var array
   */
  protected $response;

  /**
   * The configuration.
   *
   * @var Drupal\Core\Config\ImmutableConfig
   *
   * Consists of:
   * 'access_token' (the site's current access token)
   * 'client_secret' (Found on
   * https://developers.facebook.com/apps/<app id>/instagram/basic-display/).
   */
  protected $config;

  /**
   * The utexas_ig_auth object.
   *
   * @var object
   */
  protected $configFactory;

  /**
   * The Drupal-specific configuration ID.
   *
   * @var string
   */
  protected $configId;

  /**
   * The META client id.
   *
   * @var string
   */
  protected $clientId;

  /**
   * The META client secret.
   *
   * @var string
   */
  protected $clientSecret;

  /**
   * An optional override of the redirect, typically for debugging purposes.
   *
   * @var string
   */
  protected $redirectUriOverride;

  /**
   * State service object.
   *
   * @var object
   */
  protected $state;

  /**
   * The utexas_ig_auth access token.
   *
   * @var string
   */
  protected $token;

  /**
   * The utexas_ig_auth token expiration date.
   *
   * @var string
   */
  protected $expiration;

  /**
   * Base url of the website.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * The utexas_ig_auth redirect URI.
   *
   * @var string
   */
  protected $redirectUri;

  /**
   * Constructs the request object.
   */
  public function __construct($config_id) {

    $this->configFactory = \Drupal::service('utexas_instagram_api.ig_auth_service');

    $this->config = $this->configFactory->get('utexas_instagram_api.ig_auth.' . $config_id);
    $this->configId = $this->config->get('id') ?? '';
    $this->clientId = $this->config->get('client_id') ?? '';
    $this->clientSecret = $this->config->get('client_secret') ?? '';
    $this->redirectUriOverride = $this->config->get('redirect_uri_override') ?? '';

    $this->state = \Drupal::service('utexas_instagram_api.ig_state_service');
    $this->token = !$this->config->isNew() ? $this->state->get(self::STATE_API_PREFIX . $config_id . '.token') : '';
    $this->expiration = !$this->config->isNew() ? $this->state->get(self::STATE_API_PREFIX . $config_id . '.token_expiration') : '';

    $this->baseUrl = \Drupal::request()->getHost();
    $this->redirectUri = $this->redirectUriOverride ?? URL::fromRoute('entity.utexas_ig_auth.collection', [], [
      'absolute' => TRUE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigId() {
    return $this->configId;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * {@inheritdoc}
   */
  public function setToken($token) {
    $this->state->set(self::STATE_API_PREFIX . $this->configId . '.token', $token);
    $this->token = $token;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenExpiration() {
    return $this->expiration;
  }

  /**
   * {@inheritdoc}
   */
  public function setTokenExpiration($expiration) {
    $this->state->set(self::STATE_API_PREFIX . $this->configId . '.token_expiration', $expiration);
    $this->expiration = $expiration;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShortToken() {
    $this->getOauthAccessToken();
  }

  /**
   * {@inheritdoc}
   */
  public function getOauthAuthorizationCode() {

    /* See https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/business-login */
    $endpoint = "oauth/authorize?";
    $params = [
      'enable_fb_login' => '0',
      'force_authentication' => '1',
      'client_id' => $this->clientId,
      'scope' => 'instagram_business_basic',
      'redirect_uri' => $this->redirectUri,
      'response_type' => 'code',
      'state' => $this->config->get('id'),
    ];
    if (isset($this->clientId)) {
      $url = self::INSTAGRAM_API_URI . $endpoint . http_build_query($params, '', '&');
      $response = new TrustedRedirectResponse($url);
      $request = \Drupal::request();
      $response->prepare($request);
      // Make sure to trigger kernel events.
      \Drupal::service('kernel')->terminate($request, $response);
      $response->send();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOauthAccessToken() {
    /* See https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/business-login#step-2---exchange-the-code-for-a-token */
    $endpoint = "oauth/access_token";
    $params = [
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
      'grant_type' => 'authorization_code',
      'redirect_uri' => $this->redirectUri,
      'code' => $this->token,
    ];
    $response = $this->request(self::INSTAGRAM_API_URI . $endpoint, $params, 'POST');
    if (isset($response->access_token)) {
      $this->setToken($response->access_token);
      // Set token expiration to now.
      $this->setTokenExpiration(time());
      // Immediately retrieve a long-lived graph API token.
      if ($this->getGraphAccessToken()) {
        return TRUE;
      }
      return FALSE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getGraphAccessToken() {
    /* See https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/business-login#long-lived */
    $endpoint = "access_token";
    $params = [
      'grant_type' => 'ig_exchange_token',
      'client_secret' => $this->clientSecret,
      'access_token' => $this->token,
    ];

    $response = $this->request(self::INSTAGRAM_DATA_URI . $endpoint, $params, 'GET');

    if (isset($response->access_token)) {
      $this->setToken($response->access_token);
      $this->setTokenExpiration(time() + $response->expires_in);
      \Drupal::logger('utexas_instagram_api')->notice('Short-lived Instagram token exchanged for long-lived token');
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function refreshGraphAccessToken() {
    $endpoint = "refresh_access_token";
    $params = [
      'grant_type' => 'ig_refresh_token',
      'access_token' => $this->token,
    ];

    $response = $this->request(self::INSTAGRAM_DATA_URI . $endpoint, $params, 'GET');

    if (isset($response->access_token)) {
      $this->setToken($response->access_token);
      $this->setTokenExpiration(time() + $response->expires_in);
      \Drupal::logger('utexas_instagram_api')->notice('Long-lived Instagram token exchanged for new long-lived token.');
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMedia() {
    /* See https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/get-started#posts */
    $account = $this->getCurrentUserAccount();
    if (!isset($account)) {
      \Drupal::logger('utexas_instagram_api')->error('Unable to retrieve the user account');
    }
    $endpoint = $account->user_id . "/media";
    $params = [
      'access_token' => $this->token,
      'fields' => 'caption,id,media_type,media_url,permalink,thumbnail_url,timestamp,username',
      'limit' => '9',
    ];
    return $this->request(self::INSTAGRAM_DATA_URI . $endpoint, $params, 'GET');
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentUserAccount() {
    $endpoint = "me";
    $params = [
      'access_token' => $this->token,
      'fields' => 'user_id,username',
    ];
    return $this->request(self::INSTAGRAM_DATA_URI . $endpoint, $params, 'GET');
  }

  /**
   * Performs a request.
   *
   * @param string $url
   *   The base URL endpoint.
   * @param array $params
   *   API parameters to be passed to the endpoint.
   * @param string $method
   *   The HTTP request method.
   *
   * @throws \exception
   */
  protected function request($url, array $params = [], $method = 'GET') {
    $data = '';
    if (count($params) > 0) {
      if ($method == 'GET') {
        $url .= '?' . http_build_query($params, '', '&');
      }
      else {
        $data = http_build_query($params, '', '&');
      }
    }

    $headers = [];

    $headers['Authorization'] = 'Oauth';
    $headers['Content-type'] = 'application/x-www-form-urlencoded';

    /** @var \GuzzleHttp\Psr7\Response $response */
    $response = $this->doRequest($url, $headers, $method, $data);
    $status_code = $response->getStatusCode();

    if ($status_code == '200') {
      return $this->parseResponse($response->getBody());
    }
    else {
      if (!empty($response->getBody())) {
        $data = $this->parseResponse($response->getBody());
        if ($status_code != '200') {
          $error = $response->getReasonPhrase();
        }
        elseif ($meta = $data->getMetadata()) {
          $error = new \exception($meta->error_type . ': ' . $meta->error_message, $meta->code);
        }
      }
      \Drupal::logger('utexas_instagram_api')->error($error);
    }
  }

  /**
   * Actually performs a request.
   *
   * This method can be easily overriden through inheritance.
   *
   * @param string $url
   *   The url of the endpoint.
   * @param array $headers
   *   Array of headers.
   * @param string $method
   *   The HTTP method to use (normally POST or GET).
   * @param string $data
   *   An string of parameters.
   *
   * @return obj
   *   stdClass response object.
   */
  protected function doRequest($url, array $headers, $method, $data = NULL) {
    $options = [
      'headers' => $headers,
      'body' => $data,
      RequestOptions::HTTP_ERRORS => FALSE,
    ];
    $client = \Drupal::httpClient();
    $response = $client->request($method, $url, $options);

    return $response;
  }

  /**
   * Parses the response.
   */
  protected function parseResponse($response) {
    // http://drupal.org/node/985544 - json_decode large integer issue.
    $length = strlen(PHP_INT_MAX);
    $response = preg_replace('/"(id|in_reply_to_status_id)":(\d{' . $length . ',})/', '"\1":"\2"', $response);
    return json_decode($response);
  }

}
