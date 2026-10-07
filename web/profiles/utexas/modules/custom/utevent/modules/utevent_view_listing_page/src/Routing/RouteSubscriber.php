<?php

namespace Drupal\utevent_view_listing_page\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Routing\RoutingEvents;

/**
 * Listens to the dynamic route events.
 *
 * @package Drupal\utevent_view_listing_page\Routing
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
    $config = \Drupal::config('utevent_view_listing_page.config');
    if (!$config->get('display_past_events')) {
      if ($route = $collection->get('view.utevent_listing_page.past')) {
        $route->setRequirement('_access', 'FALSE');
      }
    }
    if (!$config->get('display_calendar')) {
      if ($route = $collection->get('view.utevent_calendar_page.calendar')) {
        $route->setRequirement('_access', 'FALSE');
      }
    }
  }

}
