<?php

namespace Drupal\islandora_rdm_datacite;

use Drupal\node\NodeInterface;

class Researcher {

  /**
   * @var \DOMDocument $dataciteDoct
   */
  public $dataciteDoc;

  public static function createFromJSON(string $json_data) {
    $data = \GuzzleHttp\json_decode($json_data);
    print("got here");
  }
}