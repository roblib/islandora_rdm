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

        // $rows[] = ["2222","444"];
        /* $rows[] = [['data' => $this->createChart("bar", "type")],
          ['data' => $this->createChart("bar", "owner")],
          ]; */
        $rows[] = [['data' => $this->createChart("pie", "type")],
            ['data' => $this->createChart("pie", "owner")],
        ];

        $report['chart']['content']['#rows'] = $rows;
        $report['chart']['button'] = array(
        '#type' => 'button',
        '#value' => t('switch chart'),
        '#weight' => 19,
        '#attributes' => ['id' => 'chart-toggle-button'],
        '#attached' => array(
            'library' => array('islandora_rdm_disk_usage/switch-button',),
        ),
        //$report['#attached']['library'][] = 'islandora_rdm_disk_usage/switch-button';
        );
        // \Kint::$maxLevels = 0;
        $report['table'] = $this->createTable();

        // Attach js file


        return $report;
    }

    /**
     * Create a table with the entities. Using Render arrays.
     */
    private function createTable() {
        $entities = $this->getMyList();

        $report['pageTitle'] = [
            '#type' => 'markup',
            '#markup' => '<h3>Media Items List</h3>',
        ];

        $report['table'] = [
            '#type' => 'table',
            '#header' => [
                t('Title'),
                t('Owner'),
                t('Size'),
                t('Type'),
            ],
        ];

        // Using array_keys(),array_values()function
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
            $rows[$key[$i]] = [
                'title' => $entity[$i]->get('name')->getString(),
                'owner' => $entity[$i]->get('revision_user')->getString(),
                'size' => $entity[$i]->get('field_file_size')->getString(),
                'type' => $entity[$i]->get('field_mime_type')->getString(),
            ];
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
                if ($this->exist($type, $count)) {
                    $count[$type] = $count[$type] + $size;
                } else {
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
                } else {
                    $count[$owner] = $size;
                }
            }
        }
        /* $report['content']['pageTitle'] = [
          '#type' => 'markup',
          '#markup' => '<h3> Group by ' . ucfirst($groupBy) . '</h3>',
          ]; */
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
    private function getMyList() {
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
