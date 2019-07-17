<?php

namespace Drupal\Tests\islandora_rdm_dataset\Unit;

use Drupal\islandora_rdm_datacite\Dataset;
use Drupal\Tests\UnitTestCase;

/**
 *
 */
class ExternalIdentifierTest extends UnitTestCase {

  /**
   *
   */
  public function testExportXML() {
    // $dataset = Dataset::createFromJSON();
    $json_data = file_get_contents(__DIR__ . '/../../data/testdataset.json');
    $dataset = Dataset::createFromJSON($json_data);
    $this->assertEquals(0, 1);
  }

}
