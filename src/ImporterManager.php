<?php

namespace Drupal\import_api;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Traversable;

class ImporterManager extends DefaultPluginManager {

  /**
   * ImporterManager constructor.
   *
   * @param Traversable $namespaces
   * @param CacheBackendInterface $cache_backend
   * @param ModuleHandlerInterface $module_handler
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
      'Drupal\import_api\Annotation'
    );

    $this->alterInfo('importer_info');
    $this->setCacheBackend($cache_backend, 'importer_info_plugins');
  }
}
