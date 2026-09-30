<?php

namespace Drupal\utexas_hero_carousel\Hook;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    return [
      'field__block_content__utexas_hero_carousel' => [
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Implements hook_entity_view_alter().
   */
  #[Hook('entity_view_alter')]
  public function entityViewAlter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display) {
    // Bail if not our target block bundle.
    if (!$entity instanceof BlockContent || $entity->bundle() !== 'utexas_hero_carousel') {
      return;
    }
    $id = $build['field_hero_carousel']['#attributes']['id'];
    $build['#attributes']['class'][] = 'utexas-hero-carousel';
    $build['#attributes']['class'][] = 'carousel';
    $build['#attributes']['class'][] = 'slide';
    $build['#attributes']['id'] = $id;

    if ($entity->hasField('field_hero_carousel_fade')) {
      $fade = $entity->get('field_hero_carousel_fade')->getString();
      if ($fade === '1') {
        $build['#attributes']['class'][] = 'carousel-fade';
      }
    }
    if ($entity->hasField('field_hero_carousel_autoplay')) {
      $autoplay = $entity->get('field_hero_carousel_autoplay')->getString();
    }
    // Fallback in case speed isn't set.
    $speed = 5;
    if ($entity->hasField('field_hero_carousel_speed')) {
      $speed_raw = $entity->get('field_hero_carousel_speed')->getString();
      $speed = (float) $speed_raw;
    }
    $build['#attached']['library'][] = 'utexas_hero_carousel/formatter';
    $build['#attached']['drupalSettings']['utexas_hero_carousel'][$id] = [
      'autoplay' => $autoplay,
      'interval' => $speed * 1000,
    ];
  }

}
