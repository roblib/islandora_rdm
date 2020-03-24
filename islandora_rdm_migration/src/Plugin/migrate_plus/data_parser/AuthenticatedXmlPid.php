<?php

namespace Drupal\islandora_rdm_migration\Plugin\migrate_plus\data_parser;

use Drupal\migrate_7x_claw\Plugin\migrate_plus\data_parser\AuthenticatedXml;

/**
 * Obtain XML data for migration using the XMLReader pull parser.
 * Include Fedora PID in result row for data lookups.
 *
 * @DataParser(
 *   id = "authenticated_xml_pid",
 *   title = @Translation("Authenticated XML with PID")
 * )
 */
class AuthenticatedXmlPid extends AuthenticatedXml {

  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow() {
    $target_element = array_shift($this->matches);
    // If we've found the desired element, populate the currentItem and
    // currentId with its data.
    if ($target_element !== FALSE && !is_null($target_element)) {
      foreach ($this->fieldSelectors() as $field_name => $xpath) {
        foreach ($target_element->xpath($xpath) as $value) {
          if ($value->children() && !trim((string) $value)) {
            $this->currentItem[$field_name] = $value;
          }
          elseif (!trim((string) $value)) {
            $this->currentItem[$field_name][] = $value->asXML();
          }
          else {
            $this->currentItem[$field_name][] = (string) $value;
          }
        }
      }
      // Reduce single-value results to scalars.
      foreach ($this->currentItem as $field_name => $values) {
        if (count($values) == 1) {
          $this->currentItem[$field_name] = reset($values);
        }
      }

      // Make the PID available as a field in the migration
      // This facilitates migrate_lookup, needed to migrate data
      // into paragraphs.
      if (empty($currentItem['PID'])) {
        $pid_matches = [];
        preg_match('/\/objects\/(.*?)\/datastreams/', $this->urls[$this->activeUrl], $pid_matches);
        if (!empty($pid_matches[1])) {
          $this->currentItem['PID'] = $pid_matches[1];
        }
      }
    }
  }
}
