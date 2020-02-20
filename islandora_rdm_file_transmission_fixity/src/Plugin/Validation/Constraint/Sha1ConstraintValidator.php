<?php

namespace Drupal\islandora_rdm_file_transmission_fixity\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks if sha1 is valid.
 */
class Sha1ConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      if (!preg_match('/^[a-fA-F0-9]{40}$/', $item->value)) {
        $this->context->addViolation($constraint->notSha1, ['%value' => $item->value]);
      }
    }
  }

}
