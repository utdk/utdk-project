<?php

namespace Drupal\utexas_layouts\EventSubscriber;

use Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent;
use Drupal\layout_builder\LayoutBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters render arrays for all block components.
 */
class BlockComponentRenderArray implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[LayoutBuilderEvents::SECTION_COMPONENT_BUILD_RENDER_ARRAY] = ['onBuildRender'];
    return $events;
  }

  /**
   * Add region key/value on the event.
   *
   * Due to event subscriber weighting, note that this subscriber is lower in
   * priority than the event subscriber in the layout_builder module.
   * onBuildRender() has previously been "fired" by that class.
   * see \Drupal\layout_builder\EventSubscriber\BlockComponentRenderArray.
   *
   * @param \Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent $event
   *   The section component render event.
   */
  public function onBuildRender(SectionComponentBuildRenderArrayEvent $event) {
    $region = $event->getComponent()->getRegion();

    // Get the current "build," add the key/value and reset the "build".
    // This is used in the preprocess_block Hook to modify blocks only when they
    // are placed in the "Content" region.
    $build = $event->getBuild();
    $build['#utexas_layouts_region'] = $region;
    $event->setBuild($build);
  }

}
