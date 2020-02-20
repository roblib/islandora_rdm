<?php

namespace Drupal\islandora_rdm_file_transmission_fixity\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a unique integer.
 *
 * @Constraint(
 *   id = "ValidSha1",
 *   label = @Translation("Valid SHA1", context = "Validation"),
 *   type = "string"
 * )
 */
class Sha1Constraint extends Constraint {
  /**
   * The default violation message.
   *
   * @var string
   */

  public $notSha1 = '%value is not a valid Sha1 checksum';

}
