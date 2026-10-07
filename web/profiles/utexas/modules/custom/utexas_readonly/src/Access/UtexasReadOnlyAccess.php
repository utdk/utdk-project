<?php

namespace Drupal\utexas_readonly\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\utexas_readonly\ReadOnlyHelper;

/**
 * Checks access for displaying configuration page.
 */
class UtexasReadOnlyAccess implements AccessInterface {

  /**
   * Drupal Route service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Drupal Account service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * LockFeatureAccess constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parameterized route.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowedIf(!$this->isRestrictedPath());
  }

  /**
   * Check if the current route path is restricted.
   *
   * @return bool
   *   Whether or not the path is restricted.
   */
  public function isRestrictedPath() {
    $id = FALSE;
    $readonly = FALSE;
    $parameters = $this->routeMatch->getParameters();
    $type = $parameters->get('entity_type_id');
    if (empty($type)) {
      $type = $parameters->get('image_effect');
      if (!empty($type)) {
        $type = 'image_effect';
      }
    }

    switch ($type) {
      case 'image_effect':
        $id = $parameters->get('image_style')->id();
        // 'Starts with...'.
        if (strpos($id, 'utexas_image_style') === 0) {
          $readonly = TRUE;
        }
        break;

      case 'node':
        $node_type = $parameters->get('node_type');
        if (is_string($node_type)) {
          $id = $node_type;
        }
        elseif ($node_type instanceof NodeTypeInterface) {
          $id = $node_type->id();
        }
        if (in_array($id, ReadOnlyHelper::$restrictedNodeTypes)) {
          $readonly = TRUE;
        }
        break;

      case 'media':
        $id = $parameters->get('media_type')->get('id');
        if (in_array($id, ReadOnlyHelper::$restrictedMediaTypes)) {
          $readonly = TRUE;
        }
        break;

      case 'block_content':
        $id = $parameters->get('block_content_type')->get('id');
        if (in_array($id, ReadOnlyHelper::$restrictedBlockTypes)) {
          $readonly = TRUE;
        }
        break;
    }
    if ($readonly) {
      $route_name = $this->routeMatch->getRouteName();
      // Add a warning message to ALL routes related to the given bundle.
      ReadOnlyHelper::warn();
      // Some routes should be visible, but read-only.
      foreach (ReadOnlyHelper::$viewableRoutes as $viewable_route) {
        if (strpos($route_name, $viewable_route) !== FALSE) {
          // Skip restricting access to these routes,
          // but do print a warning.
          return FALSE;
        }
      }
      // Mark all other matching routes as 'Access Denied' (403).
      return TRUE;
    }
    return FALSE;
  }

}
