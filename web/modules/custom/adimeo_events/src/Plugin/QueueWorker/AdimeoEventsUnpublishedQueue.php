<?php

declare(strict_types=1);

namespace Drupal\adimeo_events\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\NodeInterface;

/**
 * Defines a queue worker to unpublished useless events.
 *
 * @QueueWorker(
 *   id = "adimeo_events_unpublished",
 *   title = @Translation("Unpublish Events"),
 *   cron = {"time" = 60}
 * )
 */
class AdimeoEventsUnpublishedQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    if ($data instanceof NodeInterface) {
      $data->setUnpublished();
      $data->save();
    }
  }

}
