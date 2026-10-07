<?php

namespace Drupal\utexas_readonly\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\utexas_readonly\ReadOnlyHelper;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provide a list of routes that should be checked (see UtexasReadOnlyAccess).
 *
 * @package Drupal\utexas_readonly\Routing
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Ensure our route alterations occur last.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -9999];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Send restrictable routes through additional access checking.
    // See Drupal\utexas\Access\UtexasReadOnlyAccess for logic.
    foreach (ReadOnlyHelper::$restrictableRoutes as $route_name) {
      if ($route = $collection->get($route_name)) {
        $route->setRequirement('_utexas', 'TRUE');
      }
    }
  }

}
