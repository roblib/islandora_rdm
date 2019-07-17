<?php

namespace Drupal\islandora_rdm_datacite;

use DOMElement;
use Drupal\node\NodeInterface;

/**
 * A representation of a Dataset for DataCite export.
 *
 * @package Drupal\islandora_rdm_datacite
 */
class Dataset {

  /**
   * The DataCite document object.
   *
   * @var DOMDocument
   */
  public $doc;

  /**
   * Given a node, create a DataCite-formatted XML representation.
   *
   * @param Drupal\node\NodeInterface $node
   *   Node to transform to XML.
   *
   * @return bool|string
   *   DataCite-formatted XML.
   *
   * @throws Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function createFromNode(NodeInterface $node) {
    if ($node->getType() !== 'islandora_rdm_dataset') {
      return FALSE;
    }

    $this->doc = \DOMDocument::loadXml('<resource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://datacite.org/schema/kernel-4" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4.1/metadata.xsd"></resource>');

    // Identifier.
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

    // Creator.
    $creators_element = $this->doc->createElement('creators');
    $resource->appendChild($creators_element);
    $this->addContributorField($node, 'field_rdm_creator', 'creator', $creators_element);

    // Title.
    $titles_element = $this->doc->createElement('titles');
    $title_element = $this->doc->createElement('title', $node->getTitle());
    $title_element->setAttribute('xml:lang', $node->language()->getId());
    $titles_element->appendChild($title_element);
    $resource->appendChild($titles_element);

    if ($year_field = $node->get('field_rdm_publication_year')->first()) {
      $year = $this->doc->createElement('publicationYear', strtok($year_field->getValue()['value'], '-'));
      $resource->appendChild($year);
    }

    if ($publisher_field = $node->get('field_rdm_publisher')->first()) {
      $publisher_name = $publisher_field->get('entity')->getTarget()->get('field_rdm_organization_name')->first()->getString();
      $publisher = $this->doc->createElement('publisher', $publisher_name);
      $resource->appendChild($publisher);
    }

    // Contributor.
    $contributors_element = $this->doc->createElement('contributors');

    $contributor_count = $this->addContributorField($node, 'field_rdm_contributors', 'contributor', $contributors_element);
    if ($contributor_count >= 1) {
      $resource->appendChild($contributors_element);
    }

    // Dates.
    $dates_element = $this->doc->createElement('dates');
    $date_element = $this->doc->createElement('date', date('Y-m-d', $node->getChangedTime()));
    $date_element->setAttribute('dateType', 'Updated');
    $dates_element->appendChild($date_element);

    if ($additional_date_fields = $node->get('field_date_paragraph')) {
      foreach ($additional_date_fields as $additional_date_field) {
        if ($date_target = $additional_date_field->get('entity')->getTarget()) {
          $additional_date_string = $date_target->get('field_date_string')->getString();
          $additional_date_type = $date_target->get('field_type_of_date')->getString();
          if (!empty($additional_date_type) && !empty($additional_date_string)) {
            $additional_date_element = $this->doc->createElement('date', $additional_date_string);
            $additional_date_element->setAttribute('dateType', $additional_date_type);
            $dates_element->appendChild($additional_date_element);
          }
        }
      }
    }
    $resource->appendChild($dates_element);
    // Subject fields.
    $has_subjects = FALSE;
    if ($subjects_field = $node->get('field_subjects')) {
      $subjects_element = $this->doc->createElement('subjects');
      foreach ($subjects_field as $subject) {
        $subject_value = $subject->getValue();
        $has_subjects = TRUE;
        $subject_element = $this->doc->createElement('subject', $subject_value['value']);
        $subjects_element->appendChild($subject_element);
      }
      if ($has_subjects) {
        $resource->appendChild($subjects_element);
      }
    }

    // Description.
    $descriptions_element = $this->doc->createElement('descriptions');
    $description_count = 0;
    if ($descriptions_field = $node->get('field_rdm_description')) {
      foreach ($descriptions_field as $description_field) {
        if ($description_target = $description_field->get('entity')->getTarget()) {
          $description_string = $description_target->get('field_rdm_description')->getString();
          $description_type = $description_target->get('field_rdm_description_type')->getString();
          $description_element = $this->doc->createElement('description', $description_string);
          $description_element->setAttribute('descriptionType', $description_type);
          $descriptions_element->appendChild($description_element);
          $description_count += 1;
        }
      }
    }

    if ($description_count > 0) {
      $resource->appendChild($descriptions_element);
    }

    return $this->doc->saveXML();
  }

  /**
   * Format a Creator/Contributor item as XML.
   *
   * @param Drupal\node\NodeInterface $node
   *   The node being exported.
   * @param string $field_name
   *   The field name to extract.
   * @param string $element_name
   *   The type of name being extracted ('creator', 'contributor').
   * @param \DOMElement $parent_element
   *   The XML element to append to.
   *
   * @return int
   *   The number of elements added.
   */
  private function addContributorField(NodeInterface $node, $field_name, $element_name, DOMElement $parent_element) {
    $count = 0;
    foreach ($node->get($field_name) as $reference_field) {
      if ($researcher_target = $reference_field->get('entity')->getTarget()) {
        $wrapper_element = $this->doc->createElement($element_name);
        $researcher = $researcher_target->getValue();
        // Contributors have roles.
        if ($is_contributor = $researcher->getType() == 'rdm_contribution_personal') {
          $contributor_type = $researcher->get('field_rdm_role_personal')->first()->getString();
          $wrapper_element->setAttribute('contributorType', $contributor_type);
          $researcher = $researcher->get('field_rdm_person')->first()->get('entity')->getTarget()->getValue();
        }
        elseif ($is_contributor = $researcher->getType() == 'rdm_contribution_organizational') {
          $contributor_type = $researcher->get('field_rdm_role_organizational')->first()->getString();
          $wrapper_element->setAttribute('contributorType', $contributor_type);
          $researcher = $researcher->get('field_rdm_organization')->first()->get('entity')->getTarget()->getValue();
        }
        $is_person = $researcher->getType() == 'rdm_person';

        // Person.
        if ($is_person) {
          $name = $researcher->get('field_rdm_personal_name')->first()->getString();
          $name_element = $this->doc->createElement($element_name . 'Name', $name);
          $name_element->setAttribute('nameType', 'Personal');
        }
        // Organization.
        else {
          $name = $researcher->get('field_rdm_organization_name')->first()->getString();
          $name_element = $this->doc->createElement($element_name . 'Name', $name);
          $name_element->setAttribute('nameType', 'Organizational');
        }
        $wrapper_element->appendChild($name_element);
      }
      // Identifiers (person)
      if ($is_person) {
        foreach ($researcher->get('field_rdm_personal_identifier') as $person_id) {
          $person_id = $person_id->get('entity')->getTarget();
          if (!empty($person_id->get('field_rdm_identifier_string')->first()) &&
              $id = $person_id->get('field_rdm_identifier_string')->first()->getString()) {
            $scheme = $person_id->get('field_rdm_identifier_type_person')->first()->getString();
            $id_element = $this->doc->createElement('nameIdentifier', $id);
            $id_element->setAttribute('nameIdentifierScheme', $scheme);
            $wrapper_element->appendChild($id_element);
          }
        }
      }
      // Identifiers (org)
      else {
        foreach ($researcher->get('field_rdm_organizational_ids') as $org_id) {
          $org_id = $org_id->get('entity')->getTarget();
          if (!empty($org_id->get('field_rdm_identifier_string')->first()) &&
              $id = $org_id->get('field_rdm_identifier_string')->first()->getString()) {
            $scheme = $org_id->get('field_rdm_identifier_type_org')->first()->getString();
            $id_element = $this->doc->createElement('nameIdentifier', $id);
            $id_element->setAttribute('nameIdentifierScheme', $scheme);
            $wrapper_element->appendChild($id_element);
          }
        }
      }
      // Affiliation (person)
      if ($is_person && !empty($researcher->get('field_rdm_personal_affiliation')->first())) {
        // && $affiliation = $researcher->get('field_rdm_personal_affiliation')->first()->get('entity')->getTarget()) {.
        $affiliation = $researcher->get('field_rdm_personal_affiliation')->first()->get('entity')->getTarget();
        $affiliation_name = $affiliation->get('field_rdm_organization_name')->first()->getString();
        $affiliation_element = $this->doc->createElement('affiliation', $affiliation_name);
        $wrapper_element->appendChild($affiliation_element);
      }
      $parent_element->appendChild($wrapper_element);
      $count += 1;
    }
    return $count;
  }

}
