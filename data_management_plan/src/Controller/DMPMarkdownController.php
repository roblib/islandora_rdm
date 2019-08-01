<?php

namespace Drupal\data_management_plan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * Class DMPMarkdownController.
 */
class DMPMarkdownController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Symfony\Component\Serializer\SerializerInterface definition.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * Constructs a new DMPMarkdownController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, SerializerInterface $serializer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('serializer')
    );
  }

  /**
   * Exportmarkdown.
   *
   * @return string
   *   Return Hello string.
   */
  public function exportMarkdown($node = NULL) {
    $response = new Response();
    $response->headers->set('Content-type', 'text/plain');
    $entity = $this->entityTypeManager->getStorage('node')->load($node);
    $markdown = $this->serializer->serialize($entity, 'markdown');

    $response->setContent($markdown);
    return $response;
  }

}
