<?php

namespace Drupal\islandora_rdm_datacite\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jsonapi\Serializer\Serializer;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class DataciteFormatController.
 */
class DataciteFormatController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Drupal\jsonapi\Serializer\Serializer definition.
   *
   * @var \Drupal\jsonapi\Serializer\Serializer
   */
  protected $jsonapiSerializer;
  /**
   * Drupal\Core\Routing\Access\AccessInterface definition.
   *
   * @var \Drupal\Core\Routing\Access\AccessInterface
   */
  protected $accessCheckEntity;

  /**
   * Constructs a new DataciteFormatController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Serializer $jsonapi_serializer, AccessInterface $access_check_entity) {
    $this->entityTypeManager = $entity_type_manager;
    $this->jsonapiSerializer = $jsonapi_serializer;
    $this->accessCheckEntity = $access_check_entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('jsonapi.serializer'),
      $container->get('access_check.entity')
    );
  }

  /**
   * Getdatacite.
   *
   * @return string
   *   Return Hello string.
   */
  public function getDatacite($node) {
    $node = $this->entityTypeManager->getStorage('node')->load($node);
    ksm($node);
    if (empty($node)) {
      return ['#type' => 'markup', '#markup' => 'Not found'];
    }
    $response = new Response();
    $dataset = new \Drupal\islandora_rdm_datacite\Dataset();
    $xml_content = $dataset->createFromNode($node);
    $response->headers->set('Content-Type', 'text/xml');
    $response->setContent($xml_content);

    return $response;
  }

}
