<?php

namespace Drupal\islandora_rdm_datacite;

/**
 *
 */
class Researcher {

  /**
   * @var \DOMDocument
   */
  public $dataciteDoc;

  /**
   *
   */
  public static function createFromJSON(string $json_data) {
    $data = \GuzzleHttp\json_decode($json_data);
    print("got here");
  }

}
