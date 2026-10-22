<?php

namespace Drupal\utexas_scheduled_transitions\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for HourOnlyTimeConstraint.
 */
class HourOnlyTimeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if ($value === NULL || $value === '') {
      return;
    }

    if ($value instanceof \DateTime) {
      $minutes = (int) $value->format('i');
      $seconds = (int) $value->format('s');

      if ($minutes !== 0 || $seconds !== 0) {
        $this->context->addViolation($constraint->message);
      }
    }
  }

}
