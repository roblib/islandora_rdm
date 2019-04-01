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
      return FALSE;
    }

    $this->doc = \DOMDocument::loadXml('<resource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://datacite.org/schema/kernel-4" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4.1/metadata.xsd"></resource>');

    // Identifier
    $resource = $this->doc->documentElement;

    if ($identifier_field = $node->get('field_islandora_rdm_identifier')->first()) {
      $identifier = $this->doc->createElement('identifier', $identifier_field->getString());
      $identifier->setAttribute('identifierType', 'DOI');
      $resource->appendChild($identifier);
    }
    else {
      $identifier = $this->doc->createElement('identifier', 'none');
      $identifier->setAttribute('identifierType', 'DOI');
      $resource->appendChild($identifier);
    }

    if ($resource_type_field = $node->get('field_rdm_resource_type')->first()) {
      $resource_type = $this->doc->createElement('resourceType', $resource_type_field->getString());
      if ($resource_type_general_field = $node->get('field_rdm_resource_type_general')->first()) {
        $resource_type->setAttribute('resourceTypeGeneral', $resource_type_general_field->getString());
      }
      $resource->appendChild($resource_type);
    }

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

    if ($year_field = $node->get('field_rdm_publication_year')->first()) {
      $year = $this->doc->createElement('publicationYear', strtok($year_field->getValue()['value'], '-'));
      $resource->appendChild($year);
    }

    if ($publisher_field = $node->get('field_islandora_rdm_publisher')->first()) {
      $publisher = $this->doc->createElement('publisher', $publisher_field->getString());
      $resource->appendChild($publisher);
    }

    // Contributor
    $contributors_element = $this->doc->createElement('contributors');

    $contributor_count = $this->addResearcherField($node, 'field_contributors', 'contributor', $contributors_element);
    if ($contributor_count >= 1) {
      $resource->appendChild($contributors_element);
    }
    // Dates
    $dates_element = $this->doc->createElement('dates');
    $date_element = $this->doc->createElement('date', date('Y-m-d', $node->getChangedTime()));
    $date_element->setAttribute('dateType', 'Updated');
    $dates_element->appendChild($date_element);
    $resource->appendChild($dates_element);

    // Description
    $descriptions_element = $this->doc->createElement('descriptions');
    $description_element = $this->doc->createElement('description', $node->get('body')->first()->getValue()['value']);
    $description_element->setAttribute('descriptionType', $node->get('field_rdm_description_type')->getString());
    $descriptions_element->appendChild($description_element);
    $resource->appendChild($descriptions_element);

    return $this->doc->saveXML();
  }

  private function addResearcherField($node, $field_name, $element_name, $parent_element) {
    $count = 0;
    foreach ($node->get($field_name) as $reference_field) {
      if ($researcher_target = $reference_field->get('entity')->getTarget()) {
        $researcher = $researcher_target->getValue();
        $name = $researcher->get('field_name')->first()->getValue();

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
      }

      if (!empty($researcher->get('field_orcid_id')->first()) &&
          $orcid = $researcher->get('field_orcid_id')->first()->getString()) {
        $orcid_element = $this->doc->createElement('nameIdentifier', $orcid);
        $orcid_element->setAttribute('schemeURI', 'http://orcid.org/');
        $orcid_element->setAttribute('nameIdentifierScheme', 'ORCID');
        $wrapper_element->appendChild($orcid_element);
      }

      if (!empty($researcher->get('field_affiliation')->first()) 
          && $affiliation = $researcher->get('field_affiliation')->first()->getString()) {
        $affiliation_element = $this->doc->createElement('affiliation', $affiliation);
        $wrapper_element->appendChild($affiliation_element);
      }
      $parent_element->appendChild($wrapper_element);
      $count += 1;
    }
    return $count;
  }
}
