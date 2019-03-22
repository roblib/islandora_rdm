<?php

namespace Drupal\islandora_rdm_datacite\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jsonapi\Serializer\Serializer;
use Drupal\Core\Routing\Access\AccessInterface;

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
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: getDatacite with parameter(s): $node'),
    ];
  }

}
