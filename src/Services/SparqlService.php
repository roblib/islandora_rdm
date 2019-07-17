<?php

namespace Drupal\islandora_rdm\Services;

/**
 * Class SparqlService.
 *
 * @package Drupal\islandora_sparql
 */
class SparqlService {

  private $client;
  private $config;

  /**
   * Constructor.
   */
  public function __construct($client, $config) {
    $this->client = $client;
    $this->config = $config;
  }

  /**
   * To get the query results.
   */
  public function getQueryResults($query) {
    $config = $this->config->get('islandora_rdm.settings');
    $uri = $config->get('sparql_endpoint') ? $config->get('sparql_endpoint') : 'http://localhost:8080/bigdata/namespace/islandora/sparql';
    $response = $this->client->request('POST', $uri,
      [
        'headers' => [
          'Accept' => 'application/sparql-results+json, application/json',
        ],
        'form_params' => [
          'query' => $query,
        ],
      ]);
    $json = $response->getBody()->getContents();
    $results = json_decode($json);
    return $results->results->bindings;
  }

}
