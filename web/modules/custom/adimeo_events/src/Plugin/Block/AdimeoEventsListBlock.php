<?php

declare(strict_types=1);

namespace Drupal\adimeo_events\Plugin\Block;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a events list block.
 *
 * @Block(
 *   id = "adimeo_events_list_block",
 *   admin_label = @Translation("Adimeo events list block"),
 *   category = @Translation("Adimeo Events"),
 * )
 */
class AdimeoEventsListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerFactory;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];

    $current_node = $this->routeMatch->getParameter('node');
    if ($current_node instanceof NodeInterface && $current_node->getType() === 'event') {
      $field_event_id = $current_node->get('field_event_type')->getString();
      if ($field_event_id) {
        $nodes = $this->getEventsNodes(3, $field_event_id, (string) $current_node->id());
        $view_builder = $this->entityTypeManager->getViewBuilder('node');
        $events_card = $view_builder->viewMultiple($nodes, 'teaser');
        $build['content'] = $events_card;
      }
    }

    return $build;
  }

  /**
   * Helper method to get events nodes.
   *
   * @param int $length
   *   Number of elements wanted.
   * @param string $field_event_id
   *   Id of field event type (taxonomy term).
   * @param string $current_node_id
   *   Id of the current node.
   *
   * @return array
   *   Return an array of nodes.
   */
  protected function getEventsNodes(int $length, string $field_event_id, string $current_node_id): array {
    $query_all_types_nids = $nodes = [];
    $current_datetime = DrupalDateTime::createFromDateTime(new \DateTime('now'));
    $current_date_formatted = $current_datetime->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    try {
      $query = $this->entityTypeManager->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->latestRevision()
        ->condition('nid', $current_node_id, '!=')
        ->condition('type', 'event')
        ->condition('status', 1)
        ->condition('field_date_end', $current_date_formatted, '>=')
        ->condition('field_event_type', $field_event_id)
        ->sort('field_date_start')
        ->range(0, $length);
      $query_nids = $query->execute();

      $count = count($query_nids);
      if ($count < $length) {
        $query_all_types = $this->entityTypeManager->getStorage('node')->getQuery()
          ->accessCheck(TRUE)
          ->latestRevision()
          ->condition('nid', $current_node_id, '!=')
          ->condition('type', 'event')
          ->condition('status', 1)
          ->condition('field_date_end', $current_date_formatted, '>=')
          ->condition('field_event_type', $field_event_id, '!=')
          ->sort('field_date_start')
          ->range(0, $length - $count);
        $query_all_types_nids = $query_all_types->execute();
      }

      $nids = array_merge($query_nids, $query_all_types_nids);
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      $this->loggerFactory->get('adimeo_events')->error($e->getMessage());
    }

    return $nodes;
  }

}
