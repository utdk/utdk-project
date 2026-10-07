<?php

namespace Drupal\utnews_readonly\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\utnews_readonly\ReadOnlyHelper;

/**
 * Checks access for displaying configuration translation page.
 */
class ReadOnlyAccess implements AccessInterface {

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
    return AccessResult::allowedIf(!$this->restrictedPath());
  }

  /**
   * Check if the current route path is restricted.
   *
   * @return bool
   *   Whether or not the path is restricted.
   */
  public function restrictedPath() {
    if ($id = ReadOnlyHelper::getId($this->routeMatch)) {
      if (!ReadOnlyHelper::matchesReadOnlyPattern($id)) {
        return FALSE;
      }
      // Add a warning message to all routes related to the given bundle.
      ReadOnlyHelper::warn();
      $route_name = $this->routeMatch->getRouteName();
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
