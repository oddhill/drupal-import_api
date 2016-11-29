<?php

namespace Drupal\import_api\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\import_api\ImporterService;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Node Publisher that publishes nodes via a manual action triggered by an admin.
 *
 * @QueueWorker(
 *   id = "import_api_queue_worker",
 *   title = @Translation("Import API queue worker"),
 *   cron = {"time" = 60}
 * )
 */
class ImportApiQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var ImporterService
   */
  private $importerService;

  /**
   * ImportApiQueueWorker constructor.
   *
   * {@inheritdoc}
   *
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ImporterService $importerService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->importerService = $importerService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('import_api.importer_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if ($data instanceof ImporterPluginBase) {
      $this->importerService->createBatchFor($data);
    }
  }
}
