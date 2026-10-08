<?php

namespace Drupal\utnews_view_listing_page\StackMiddleware;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\page_cache\StackMiddleware\PageCache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Do not use facets' 'f' query parameter in constructing Cache ID for /news.
 */
class FacetCacheSkip extends PageCache {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|null
   */
  protected ?EventDispatcherInterface $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(HttpKernelInterface $http_kernel, CacheBackendInterface $cache, RequestPolicyInterface $request_policy, ResponsePolicyInterface $response_policy, ?EventDispatcherInterface $event_dispatcher = NULL) {
    parent::__construct($http_kernel, $cache, $request_policy, $response_policy);
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheId(Request $request) {
    // This is the method we are modifying. It sends the request URI to our
    // helper method, $this->clear(), which strips the 'f' query parameter
    // and returns the "clean" result for the purposes of the Drupal page cache.
    if (!isset($this->cid)) {
      $cleared = $this->clear($request->getRequestUri());
      $cid_parts = [
        $request->getSchemeAndHttpHost() . $cleared,
        $request->getRequestFormat(NULL),
      ];
      $this->cid = implode(':', $cid_parts);
    }
    return $this->cid;
  }

  /**
   * The actual business logic. Strip out 'f' query parameters for /news.
   *
   * @param string $value
   *   The value to cleanup.
   *
   * @return string
   *   The cleared value.
   */
  protected function clear($value) {
    $request_parts = UrlHelper::parse($value);
    if (($request_parts['path'] !== '/news')) {
      return $value;
    }
    if (empty($request_parts['query'])) {
      return $value;
    }
    $request_uri = '';
    if (!empty($request_parts['path'])) {
      $request_uri .= $request_parts['path'];
    }
    // Explicitly remove any 'f' query arguments.
    $request_query = UrlHelper::filterQueryParameters($request_parts['query'], ['f']);
    if (!empty($request_query)) {
      // Sort the query parameters to minimize cache variants.
      ksort($request_query);
      $request_uri .= '?' . UrlHelper::buildQuery($request_query);
    }
    return $request_uri;
  }

}
