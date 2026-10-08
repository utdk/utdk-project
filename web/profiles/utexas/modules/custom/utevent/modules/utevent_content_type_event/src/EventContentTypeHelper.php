<?php

namespace Drupal\utevent_content_type_event;

use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;

/**
 * Business logic for rendering the content type.
 */
class EventContentTypeHelper {

  /**
   * A simple array of matching taxonomy terms.
   *
   * @param Drupal\taxonomy\Entity\Term $entity
   *   The node object.
   * @param string $query
   *   Query identifier associated with this reference.
   *
   * @return array
   *   A simple array of matching taxonomy terms.
   */
  public static function prepareEventTaxonomy(Term $entity, $query) {
    $url = Url::fromUri('internal:/events?' . $query . '=' . $entity->id());
    $title = $entity->getName();
    $output = Link::fromTextAndUrl($title, $url)->toString();
    return Markup::create($output);
  }

}
