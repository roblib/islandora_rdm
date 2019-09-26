<?php

namespace Drupal\islandora_rdm_workflows\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Provides a 'Workflow Participants' condition. The block will still be shown on all
 * other pages, including non-content types. This differs from the negated
 * condition "Content types", which will only be evaluated on node pages, which
 * means the block won't be shown on other pages like views.
 *
 * @Condition(
 *   id = "workflow_state",
 *   label = @Translation("Workflow State")
 * )
 */
class WorkflowState extends ConditionPluginBase implements ContainerFactoryPluginInterface{

  /**
   * The EntityTypeManager object.
   *
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The CurrentRouteMatch object.
   *
   * @var CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * Creates a new WorkflowState instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param CurrentRouteMatch $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager, CurrentRouteMatch $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Create list of content types.
    $options = [];
    $workflows = $this->entityTypeManager->getStorage('workflow')
      ->loadMultiple();
    foreach ($workflows as $workflow) {
      foreach ($workflow->getTypePlugin()->getStates() as $state) {
        $options[$workflow->id() . '-' . $state->id()] = $workflow->label() . ' - ' . $state->label();
      }
    }
    $form['bundles'] = [
      '#title' => $this->t('Node types'),
      '#description' => $this->t('The content types to hide the block on. The block will still be shown on all other pages, including non-content types.<br>This differs from the negated condition "Content types", which will only be evaluated on node pages, which means the block won\'t be shown on other pages like views.'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->configuration['bundles'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['bundles'] = array_filter($form_state->getValue('bundles'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (count($this->configuration['bundles']) > 1) {
      $bundles = $this->configuration['bundles'];
      $last = array_pop($bundles);
      $bundles = implode(', ', $bundles);
      return $this->t('The node bundle is @bundles or @last', [
        '@bundles' => $bundles,
        '@last' => $last,
      ]);
    }
    $bundle = reset($this->configuration['bundles']);
    return $this->t('The node bundle is @bundle', ['@bundle' => $bundle]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // Check if a setting has been set.
    if (empty($this->configuration['bundles'])) {
      return TRUE;
    }

    // Check if we are dealing with a node.
    $node = $this->routeMatch->getParameter('node');
    ksm($node->get('moderation_state')->getDataDefinition()); //->getString());
    if (is_scalar($node)) {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->load($node);
    }

    if (empty($node)) {
      return TRUE;
    }

    return empty($this->configuration['bundles'][$node->getType()]);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['bundles' => []] + parent::defaultConfiguration();
  }
}
