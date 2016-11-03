<?php

namespace Drupal\import_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\import_api\ImporterManager;
use Drupal\import_api\ImporterService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportController implements ContainerInjectionInterface {

  /**
   * @var ImporterManager
   */
  private $importerManager;

  /**
   * @var ImporterService
   */
  private $importerService;

  /**
   * ImportController constructor.
   *
   * @param ImporterManager $importerManager
   * @param ImporterService $importerService
   */
  public function __construct(
    ImporterManager $importerManager,
    ImporterService $importerService
  ) {
    $this->importerManager = $importerManager;
    $this->importerService = $importerService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.importer'),
      $container->get('import_api.importer_service')
    );
  }

  /**
   * Renders a list of importer plugins.
   *
   * @return array
   */
  public function listImporters() {
    //$plugin_definitions = $this->importerManager->getDefinitions();

    $output = [
      '#type' => 'table',
      '#header' => ['Importer', 'Imported every', 'Last import', 'Actions'],
      '#empty' => new TranslatableMarkup('There are no importers.'),
    ];

    $output[] = [
      'title' => [
        '#markup' => 'Testing',
      ],
      'imported_every' => [
        '#markup' => '10',
      ],
      'last_import' => [
        '#markup' => 'Today at 13:37',
      ],
      'operations' => [
        '#type' => 'operations',
        '#links' => [
          'import' => [
            'title' => 'Import',
            'url' => Url::fromRoute('import_api.admin_config_importers_import'),
          ],
        ],
      ],
    ];

    return $output;
  }

  public function triggerImporter() {
    batch_set([
      'title' => new TranslatableMarkup('Testing batch'),
      'operations' => [
        ['import_api_batch_1', ['a string', ['foo' => 'bar']]],
      ],
      'finished' => 'import_api_batch_finished',
      'file' => drupal_get_path('module', 'import_api') . '/import_api.module',
    ]);

    return batch_process(Url::fromRoute('import_api.admin_config_importers'));
  }
}
