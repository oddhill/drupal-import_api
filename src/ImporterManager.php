<?php

namespace Drupal\import_api;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Traversable;

class ImporterManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/Importer',
      $namespaces,
      $module_handler,
      'Drupal\import_api\Contract\ImporterInterface',
      'Drupal\import_api\Annotation\Importer'
    );

    $this->alterInfo('importer_info');
    $this->setCacheBackend($cache_backend, 'importer_info_plugins');
  }

  /**
   * Get an array of importers that should be queued for processing.
   *
   * @return ImporterPluginBase[]
   */
  public function getImportersToQueue() {
    $importers = [];

    foreach ($this->getImporterInstances() as $plugin_id => $importer) {
      if ($importer->shouldBeQueued() && $importer->getQueuedAt() === 0) {
        $importers[$plugin_id] = $importer;
      }
    }

    return $importers;
  }

  /**
   * Get an array of importer plugin instances.
   *
   * @return ImporterPluginBase[]
   */
  public function getImporterInstances() {
    $instances = [];

    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      $instances[$plugin_id] = $this->createInstance($plugin_id);
    }

    return $instances;
  }
}
