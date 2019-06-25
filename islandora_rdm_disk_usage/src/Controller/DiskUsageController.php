<?php

namespace Drupal\islandora_rdm_disk_usage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class DiskUsageController. All helper methods are private.
 */
class DiskUsageController extends ControllerBase {

  /**
   * An instance variable.
   *
   * @var entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DiskUsageController object.
   */
  public function __construct(EntityTypeManagerInterface $manager) {
    $this->entityTypeManager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
              $container->get('entity_type.manager')
      );
  }

  /**
   * To create a report and it returns a render array.
   */
  public function createReports() {
    $report = ['report'];
    $report['report']['table'] = $this->createTable();

    $report['report']['chart']['pageTitle'] = [
      '#type' => 'markup',
      '#markup' => '<h3>Charts</h3>',
    ];
    $report['report']['chart']['content'] = [
      '#type' => 'table',
      '#header' => [
        t('Bar Chart'),
        t('Pie Chart'),
      ],
    ];

    // $rows[] = ["2222","444"];
    $rows[] = [['data' => $this->createChart("bar", "type")],
          ['data' => $this->createChart("pie", "type")],
    ];
    $rows[] = [['data' => $this->createChart("bar", "owner")],
          ['data' => $this->createChart("pie", "owner")],
    ];

    $report['report']['chart']['content']['#rows'] = $rows;
    // \Kint::$maxLevels = 0;
    // ksm($report);
    return $report;
  }

  /**
   * Create a table with the entities. Using Render arrays.
   */
  private function createTable() {
    $entities = $this->getMyList();

    $report = ['content'];
    $report['content']['pageTitle'] = [
      '#type' => 'markup',
      '#markup' => '<h3>Media Items List</h3>',
    ];
    $report['content']['table'] = [
      '#type' => 'table',
      '#header' => [
        t('Title'),
        t('Owner'),
        t('Size'),
        t('Type'),
      ],
    ];

    $rows = [];
    foreach ($entities as $key => $entity) {
      $rows[$key] = [
        'title' => $entity->get('name')->getString(),
        'owner' => $entity->get('revision_user')->getString(),
        'size' => $entity->get('field_file_size')->getString(),
        'type' => $entity->get('field_mime_type')->getString(),
      ];
    }
    $report['content']['table']['#rows'] = $rows;
    return $report;
  }

  // Create a chart with the entities. Using ChartJS API module.

  /**
   * Var: $chartType: a string; $groupBy:"owner"/"type".
   */
  private function createChart($chartType, $groupBy) {
    $entities = $this->getMyList();

    // An array to count the size of each media type.
    $count = [];
    // Add sizes.
    foreach ($entities as $key => $entity) {
      $size = $entity->get('field_file_size')->value;
      $owner = $entity->get('revision_user')->getString();
      $typeFull = $entity->get('field_mime_type')->getString();
      $type = strtok($typeFull, '/');

      if ($groupBy == "type") {
        // Echo "before adding: type: " . $type . "
        // size: " . $count[$type] . "<br>";
        // echo "to be added: type: " . $type . "
        // size: " . $size . "<br>";.
        if ($this->exist($type, $count)) {
          $count[$type] = $count[$type] + $size;
        }
        else {
          $count[$type] = $size;
        }
        // Echo "after adding: type: " . $type . "
        // size: " . $count[$type] . "<br><br>";.
      }
      // Group by owner.
      else {
        if ($owner == "") {
          $owner = "others";
        }
        if ($this->exist($owner, $count)) {
          $count[$owner] = $count[$owner] + $size;
        }
        else {
          $count[$owner] = $size;
        }
      }
    }

    $report = ['content'];
    $report['content']['pageTitle'] = [
      '#type' => 'markup',
      '#markup' => '<h3> Group by ' . ucfirst($groupBy) . '</h3>',
    ];
    $report['content']['chart'] = [
      '#data' => [
        'labels' => array_keys($count),
        'datasets' => [
                  [
                    'label' => 'Media Items',
                    'data' => array_values($count),
                    'backgroundColor' => ['#00557f', '#f8413c', '#666666'],
                    'hoverBackgroundColor' => ['#004060', '#9b2926', '#333333'],
                  ],
        ],
      ],
      '#graph_type' => $chartType,
      '#id' => 'my' . $chartType . $groupBy,
      '#type' => 'chartjs_api',
    ];
    return $report;
  }

  /**
   * Query with the entity_type.manager, load entities in Drupal code.
   */
  private function getMyList() {
    $query = $this->entityTypeManager->getStorage('media');

    // Get An array of entity ID of all published materials.
    $query_all = $query->getQuery()
      ->condition('status', 1)
      ->execute();

    // Load the entities, loadMultiple($ids):
    // array $ids: An array of entity ID, or NULL to load all entities.
    $list = \Drupal::entityTypeManager()
      ->getStorage('media')
      ->loadMultiple($query_all);
    // ksm($list); //view the value on the page.
    return $list;
  }

  // Helper method to decide if a type exists in the array.

  /**
   * Var: $type: string, $array: array; return Boolean.
   */
  private function exist($type, $array) {
    foreach ($array as $key => $value) {
      if ($key == $type) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
