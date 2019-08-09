<?php

namespace Drupal\islandora_rdm_datacite\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\islandora_rdm_datacite\Dataset;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DataciteFormatController.
 */
class DataciteFormatController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Routing\Access\AccessInterface definition.
   *
   * @var Drupal\Core\Routing\Access\AccessInterface
   */
  protected $accessCheckEntity;

  /**
   * DataciteFormatController constructor.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity storage.
   * @param Drupal\Core\Routing\Access\AccessInterface $access_check_entity
   *   Access checker.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccessInterface $access_check_entity) {
    $this->entityTypeManager = $entity_type_manager;
    $this->accessCheckEntity = $access_check_entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('access_check.entity')
    );
  }

  /**
   * Getdatacite.
   *
   * @param $node
   *   Node to transform to DataCite format.
   *
   * @return array|Response
   *   DataCite-formatted node.
   *
   * @throws Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getDatacite($node) {
    $node = $this->entityTypeManager->getStorage('node')->load($node);

    if (empty($node)) {
      return ['#type' => 'markup', '#markup' => 'Not found'];
    }
    $response = new Response();
    $dataset = new Dataset();
    $xml_content = $dataset->createFromNode($node);
    $response->headers->set('Content-Type', 'text/xml');
    $response->setContent($xml_content);

    return $response;
  }

}
