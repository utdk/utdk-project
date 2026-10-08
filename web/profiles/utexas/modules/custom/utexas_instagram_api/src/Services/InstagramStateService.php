<?php

namespace Drupal\utexas_instagram_api\Services;

use Drupal\Core\State\State;

/**
 * Provides an Instagram State Service.
 *
 * @package Drupal\utexas_instagram_api\Services
 */
class InstagramStateService {

  /**
   * State.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Constructor.
   */
  public function __construct(State $state) {
    $this->state = $state;
  }

  /**
   * Get.
   */
  public function get($name) {
    return $this->state->get($name);
  }

  /**
   * Set.
   */
  public function set($key, $value) {
    $this->state->set($key, $value);
  }

}
