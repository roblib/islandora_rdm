<?php

namespace Drupal\islandora_rdm_datacite;

use Drupal\node\NodeInterface;

class Dataset {

  /**
   * @var \DOMDocument $doc
   */
  public $doc;

  public function createFromNode(NodeInterface $node) {
    if ($node->getType() !== 'islandora_rdm_dataset') {
      ksm($node->getType());
      return FALSE;
    }
    $xsins = 'http://www.w3.org/2001/XMLSchema-instance';
    $this->doc = \DOMDocument::loadXml('<resource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://datacite.org/schema/kernel-4" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4.1/metadata.xsd"></resource>');

    // Identifier
    $resource = $this->doc->documentElement;
    $identifier = $this->doc->createElement('identifier', $node->get('field_islandora_rdm_identifier')->first()->getString());
    $identifier->setAttribute('identifierType', 'DOI');
    $resource->appendChild($identifier);
    return $this->doc->saveXML();
  }
}