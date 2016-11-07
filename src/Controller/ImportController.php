<?php

namespace Drupal\import_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\import_api\ImporterManager;
use Drupal\import_api\ImporterService;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    $plugin_definitions = $this->importerManager->getDefinitions();

    $header = [
      'importer' => new TranslatableMarkup('Importer'),
      'imported_every' => new TranslatableMarkup('Imported every'),
      'last_import' => new TranslatableMarkup('Last import'),
      'actions' => new TranslatableMarkup('Actions'),
    ];

    $output = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => new TranslatableMarkup('There are no importers.'),
    ];

    foreach ($plugin_definitions as $plugin_id => $definition) {
      $output[$plugin_id] = [
        'title' => [
          '#markup' => $definition['label'],
        ],
        'imported_every' => [
          '#markup' => 'N/A',
        ],
        'last_import' => [
          '#markup' => 'N/A',
        ],
        'operations' => [
          '#type' => 'operations',
          '#links' => [
            'import' => [
              'title' => new TranslatableMarkup('Import'),
              'url' => Url::fromRoute('import_api.admin_config_importers_import', [
                'plugin_id' => $plugin_id,
              ]),
            ],
          ],
        ],
      ];
    }

    return $output;
  }

  /**
   * Starts a batch process for the specified importer plugin.
   *
   * @param $plugin_id
   * @return null|RedirectResponse
   */
  public function triggerImporter($plugin_id) {
    $redirect_url = Url::fromRoute('import_api.admin_config_importers');

    /** @var ImporterPluginBase $importer */
    $importer = $this->importerManager
      ->createInstance($plugin_id);

    $this->importerService
      ->createBatchFor($importer);

    return $this->importerService
      ->batchProcess($redirect_url);
  }
}
