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

    $report['chart']['pageTitle'] = [
      '#type' => 'markup',
      '#markup' => '<h3>Charts</h3>',
    ];
    $report['chart']['content'] = [
      '#type' => 'table',
      '#header' => [
        t('Group by Type'),
        t('Group by Owner'),
      ],
    ];
    $rows[] = [['data' => $this->createChart("pie", "type")],
          ['data' => $this->createChart("pie", "owner")],
    ];
    $report['chart']['content']['#rows'] = $rows;
    $report['table'] = $this->createTable();
    return $report;
  }

  /**
   * Create a table with the entities. Using Render arrays.
   */
  private function createTable() {
    $entities = $this->getMediaEntityList();

    $report['pageTitle'] = [
      '#type' => 'markup',
      '#prefix' => '<h3>',
      '#markup' => $this->t('Media Items List'),
      '#suffix' => '</h3>',
    ];

    $report['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Owner'),
        $this->t('Size'),
        $this->t('Type'),
      ],
    ];

    $key = array_keys($entities);
    $entity = array_values($entities);

    // Get current page.
    $page = \Drupal::request()->query->get('page', '');
    // Starts from 0.
    $current_page = $page;
    $num_per_page = 5;

    $begin = $current_page * $num_per_page;
    $end = $current_page * $num_per_page + $num_per_page;

    if ($end >= count($entities)) {
      $end = count($entities);
    }
    for ($i = $begin; $i < $end; $i++) {
      if (($entity[$i]->hasField('name'))
                && ($entity[$i]->hasField('revision_user'))
                && ($entity[$i]->hasField('field_file_size'))
                && ($entity[$i]->hasField('field_mime_type'))) {
        $rows[$key[$i]] = [
          'title' => $entity[$i]->get('name')->getString(),
          'owner' => $entity[$i]->get('revision_user')->getString(),
          'size' => $entity[$i]->get('field_file_size')->getString(),
          'type' => $entity[$i]->get('field_mime_type')->getString(),
        ];
      }
      else {
        continue;
      }
    }
    $report['table']['#rows'] = $rows;

    // Now that we have the total number of results
    // Create and initialize the pager.
    pager_default_initialize(count($entities), $num_per_page);

    // Finally, add the pager to the render array, and return.
    // Pager_default_initialize sets some global variables so that
    // The pager theme code 'pager' knows how many page links to create
    // The pager theme code also create the html code.
    $report['pager'] = ['#type' => 'pager'];

    return $report;
  }

  /**
   * Create a chart with the entities. Using ChartJS API module.
   * Var: $chartType: a string; $groupBy:"owner"/"type".
   */
  private function createChart($chartType, $groupBy) {
    $entities = $this->getMediaEntityList();

    // An array to count the size of each media type.
    $count = [];
    // Add sizes.
    foreach ($entities as $key => $entity) {

      if (($entity->hasField('field_file_size'))
                && ($entity->hasField('revision_user'))
                && ($entity->hasField('field_mime_type'))) {
        $size = $entity->get('field_file_size')->value;
        $owner = $entity->get('revision_user')->getString();
        $type_full = $entity->get('field_mime_type')->getString();
        $type = strtok($type_full, '/');

        if ($groupBy == "type") {
          if ($this->exist($type, $count)) {
            $count[$type] = $count[$type] + $size;
          }
          else {
            $count[$type] = $size;
          }
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
      else {
        continue;
      }
    }

    $report = [
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
  private function getMediaEntityList() {
    $query = $this->entityTypeManager->getStorage('media');

    // Get An array of entity ID of all published materials.
    $query_all = $query->getQuery()
      ->condition('status', 1)
      ->sort('field_file_size', 'DESC')
      ->execute();

    // Load the entities, loadMultiple($ids):
    // array $ids: An array of entity ID, or NULL to load all entities.
    $list = \Drupal::entityTypeManager()
      ->getStorage('media')
      ->loadMultiple($query_all);
    // ksm($list); //view the value on the page.
    return $list;
  }

  /**
   * Helper method to decide if a type exists in the array.
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
