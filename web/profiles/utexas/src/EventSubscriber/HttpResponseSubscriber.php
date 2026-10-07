<?php

namespace Drupal\utexas\EventSubscriber;

use Drupal\Core\Cache\CacheableResponseInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * HTTP Response subscriber.
 */
class HttpResponseSubscriber implements EventSubscriberInterface {

  /**
   * Sets extra headers on any responses, also subrequest ones.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onAllResponds(ResponseEvent $event) {
    $response = $event->getResponse();
    // Always add the 'http_response' cache tag to be able to invalidate every
    // response, for example after rebuilding routes.
    if ($response instanceof CacheableResponseInterface) {
      $response->getCacheableMetadata()->addCacheTags(['http_response']);
    }
  }

  /**
   * Sets extra headers on successful responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespond(ResponseEvent $event) {
    if (!$event->isMainRequest()) {
      return;
    }

    $response = $event->getResponse();

    // Set the UTDK version header.
    $response->headers->set('X-Utexas-Drupal-Kit', '3');
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    // There is no specific reason for choosing 16 beside it should be executed
    // before ::onRespond().
    $events[KernelEvents::RESPONSE][] = ['onAllResponds', 16];
    return $events;
  }

}
