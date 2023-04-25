<?php

declare(strict_types=1);

namespace Drupal\adimeo_events\Service;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Helper functions for adimeo events.
 */
class AdimeoEventsHelper {

  /**
   * The block manager interface.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected BlockManagerInterface $blockManagerInterface;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager_interface
   *   The block manager interface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger.
   */
  public function __construct(
    BlockManagerInterface $block_manager_interface,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->blockManagerInterface = $block_manager_interface;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Method to get plugin block.
   *
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return array|null
   *   Return the block or null.
   */
  public function getPluginBlock(string $plugin_id): ?array {
    try {
      $config = [];
      /** @var \Drupal\adimeo_events\Plugin\Block\AdimeoEventsListBlock $events_list_block */
      $events_list_block = $this->blockManagerInterface->createInstance($plugin_id, $config);
      return $events_list_block->build();
    }
    catch (PluginException $e) {
      $this->loggerFactory->get('adimeo_events')->error($e->getMessage());
    }

    return NULL;
  }

}
