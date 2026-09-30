<?php

namespace Drupal\utexas_instagram_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\utexas_instagram_api\UTexasInstagramApi;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a controller to list entity instances.
 */
class InstagramAuthListController extends ControllerBase {

  /**
   * Provides the listing page for the account authorization entity type.
   *
   * Note that we are extending ControllerBase rather than
   * Drupal\Core\Entity\Controller\EntityListController. This is because the
   * listing() method found in EntityListController is "not compatible" with our
   * new one here.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function listing(?Request $request = NULL) {

    // Get URL paramters following successful Instagram Account OAuth request.
    $code = $request->query->get('code', NULL);
    $state = $request->query->get('state', NULL);

    if (!empty($code) && !empty($state)) {
      $entities = $this->entityTypeManager()->getStorage('utexas_ig_auth')->loadByProperties(['id' => $state]);
      $ig_auth = reset($entities);

      if (!empty($ig_auth)) {
        $instagram_request = new UTexasInstagramApi($ig_auth->id());
        $instagram_request->setToken($code);
        $instagram_request->getOauthAccessToken();
      }
    }
    return $this->entityTypeManager()->getListBuilder('utexas_ig_auth')->render();
  }

}
