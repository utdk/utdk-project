<?php

namespace Drupal\utprof_readonly\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\utprof_readonly\ReadOnlyHelper;

/**
 * Listens to the dynamic route events.
 *
 * @package Drupal\utprof_readonly\Routing
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
    // See Drupal\utprof\Access\LockAccess for logic.
    foreach (ReadOnlyHelper::$restrictableRoutes as $route_name) {
      if ($route = $collection->get($route_name)) {
        $route->setRequirement('_utprof', 'TRUE');
      }
    }
  }

}
