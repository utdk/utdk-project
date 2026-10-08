<?php

namespace Drupal\utexas_scheduled_transitions\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that a datetime is set to the top of the hour.
 *
 * @Constraint(
 *   id = "HourOnlyTime",
 *   label = @Translation("Hour only time"),
 *   type = "datetime"
 * )
 */
class HourOnlyTimeConstraint extends Constraint {

  /**
   * Error message when minutes or seconds are not 00.
   *
   * @var string
   */
  public $message = 'Scheduled transitions must occur at the top of the hour (minutes and seconds must be 00:00).';

}
