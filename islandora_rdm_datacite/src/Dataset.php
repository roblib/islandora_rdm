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

    // Creator
    $creators_element = $this->doc->createElement('creators');
    $resource->appendChild($creators_element);
    $this->addResearcherField($node, 'field_creator', 'creator', $creators_element);

    // Title
    $titles_element = $this->doc->createElement('titles');
    $title_element = $this->doc->createElement('title', $node->getTitle());
    $title_element->setAttribute('xml:lang', $node->language()->getId());
    $titles_element->appendChild($title_element);
    $resource->appendChild($titles_element);

    // Contributor
    $contributors_element = $this->doc->createElement('contributors');
    $resource->appendChild($contributors_element);
    $this->addResearcherField($node, 'field_contributors', 'contributor', $contributors_element);

    // Dates
    $dates_element = $this->doc->createElement('dates');
    $date_element = $this->doc->createElement('date', date('Y-m-d', $node->getChangedTime()));
    $date_element->setAttribute('dateType', 'Updated');
    $dates_element->appendChild($date_element);
    $resource->appendChild($dates_element);

    // Description
    $descriptions_element = $this->doc->createElement('descriptions');
    $description_element = $this->doc->createElement('description', $node->get('body')->getValue()['value']);
    $description_element->setAttribute('descriptionType', $node->get('field_rdm_description_type')->getString());
    $descriptions_element->appendChild($description_element);
    $resource->appendChild($descriptions_element);

    return $this->doc->saveXML();
  }

  private function addResearcherField($node, $field_name, $element_name, $parent_element) {
    foreach ($node->get($field_name) as $reference_field) {
      $researcher = $reference_field->get('entity')->getTarget()->getValue();
      $name = $researcher->get('field_name')->first()->getValue();
      ksm($name);
      $formatted_name =  \Drupal::service('name.formatter')->format($name);
      $wrapper_element =  $this->doc->createElement($element_name);
      $name_element = $this->doc->createElement($element_name . 'Name', $formatted_name);
      $wrapper_element->appendChild($name_element);
      foreach(['given', 'family'] as $name_segment) {
        if (!empty($name[$name_segment])) {
          $name_segment_element = $this->doc->createElement($name_segment . 'Name', $name[$name_segment]);
          $wrapper_element->appendChild($name_segment_element);
        }
      }

      if ($orcid = $researcher->get('field_orcid_id')->first()->getString()) {
        $orcid_element = $this->doc->createElement('nameIdentifier', $orcid);
        $orcid_element->setAttribute('schemeURI', 'http://orcid.org/');
        $orcid_element->setAttribute('nameIdentifierScheme', 'ORCID');
        $wrapper_element->appendChild($orcid_element);
      }

      if ($affiliation = $researcher->get('field_affiliation')->first()->getString()) {
        $affiliation_element = $this->doc->createElement('affiliation', $affiliation);
        $wrapper_element->appendChild($affiliation_element);
      }
      $parent_element->appendChild($wrapper_element);
    }
  }
}