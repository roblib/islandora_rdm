<?php

namespace Drupal\islandora_rdm_datacite\Plugin\Action;

use Drupal\islandora_datacite_doi\Plugin\Action\MintDataciteDOINode;
use Drupal\islandora_rdm_datacite\Dataset;

/**
 * Mints a Datacite DOI for noes.
 *
 * @Action(
 *   id = "dgi_actions_mint_rdm_datacite_doi_node",
 *   label = @Translation("Mint a Datacite DOI for Nodes using RDM's custom XML."),
 *   type = "node"
 * )
 */
class MintRDMDataciteDOI extends MintDataciteDOINode {

  protected function getFieldData(): array {
    $data = [];
    $entity = $this->getEntity();
    $dataset = new Dataset();
    $data['xml'] = $dataset->createFromNode($entity);
    return $data;
  }

}
