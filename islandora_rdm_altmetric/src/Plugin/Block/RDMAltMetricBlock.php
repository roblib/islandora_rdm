<?php

namespace Drupal\islandora_rdm_altmetric\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'RDMAltMetricBlock' block.
 *
 * @Block(
 *  id = "rdm_altmetric_block",
 *  admin_label = @Translation("RDM AltMetric Block"),
 *  context = {
 *    "node" = @ContextDefinition("entity:node")
 *  }
 * )
 */
class RDMAltMetricBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RDMAltMetricBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface definition.
   */
  public function __construct(
            array $configuration,
            $plugin_id,
            $plugin_definition,
            EntityTypeManagerInterface $entity_type_manager
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
              $configuration,
              $plugin_id,
              $plugin_definition,
              $container->get('entity_type.manager')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $node = $this->getContextValue('node');

    if ($node->bundle() === 'islandora_rdm_dataset') {
      if ($doi = $node->get('field_islandora_rdm_identifier')->first()) {
        $doi_value = $doi->getValue()['value'];
        if (!empty($doi_value)) {

          $build['#theme'] = 'rdm_altmetric_block';
          $build['rdm_altmetric_block']['#markup'] = 'Implement RDMAltMetricBlock.';

          $build['altmetric'] = [
            '#type' => 'markup',
            '#markup' => '<h3>Altmetric</h3>',
            '#weight' => 255,
          ];
          $build['#attached']['library'][] = 'islandora_rdm_altmetric/altmetric';
          $build['rdm_altmetric_block'] = [
            '#weight' => 255,
            '#type' => 'container',
            '#attributes' => [
              'class' => ['rdm_altmetric_block'],
            ],
            'links' => [
              '#theme' => 'item_list',
              '#attributes' => [
                'class' => ['inline'],
              ],
            ],
          ];
          $build['rdm_altmetric_block']['altmetric'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => [
              'class' => 'altmetric-embed',
              'data-badge-type' => 'medium-donut',
              'data-badge-details' => 'right',
              'data-condensed' => 'false',
              'data-doi' => $doi_value,
            ],
          ];
        }
      }
    }

    return $build;
  }

}
