<?php

namespace Drupal\import_api\Controller;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\import_api\ImporterManager;
use Drupal\import_api\ImporterRemoveService;
use Drupal\import_api\ImporterService;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ImportController implements ContainerInjectionInterface {

  /**
   * @var DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * @var ImporterManager
   */
  private $importerManager;

  /**
   * @var ImporterService
   */
  private $importerService;

  /**
   * @var ImporterRemoveService
   */
  private $importerRemoveService;

  /**
   * ImportController constructor.
   *
   * @param DateFormatterInterface $dateFormatter
   * @param ImporterManager $importerManager
   * @param ImporterService $importerService
   * @param ImporterRemoveService $importerRemoveService
   */
  public function __construct(
    DateFormatterInterface $dateFormatter,
    ImporterManager $importerManager,
    ImporterService $importerService,
    ImporterRemoveService $importerRemoveService
  ) {
    $this->dateFormatter = $dateFormatter;
    $this->importerManager = $importerManager;
    $this->importerService = $importerService;
    $this->importerRemoveService = $importerRemoveService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('plugin.manager.importer'),
      $container->get('import_api.importer_service'),
      $container->get('import_api.importer_remove_service')
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
      /** @var ImporterPluginBase $importer */
      $importer = $this->importerManager->createInstance($plugin_id);

      $output[$plugin_id] = [
        'title' => [
          '#markup' => $definition['label'],
        ],
        'imported_every' => [
          '#markup' => new TranslatableMarkup('@minutes minutes', [
            '@minutes' => $importer->getCronIntervalTime() / 60,
          ]),
        ],
        'last_import' => [
          '#theme' => 'time',
          '#timestamp' => $importer->getLastRunAt(),
          '#text' => new TranslatableMarkup('%time ago', [
            '%time' => $this->dateFormatter->formatTimeDiffSince($importer->getLastRunAt()),
          ]),
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
            'remove' => [
              'title' => new TranslatableMarkup('Remove'),
              'url' => Url::fromRoute('import_api.admin_config_importers_remove', [
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

    // Create a batch instance for the supplied importer.
    $this->importerService
      ->createBatchFor($importer);

    // Start the batch process.
    return $this->importerService
      ->batchProcess($redirect_url);
  }

  /**
   * Starts a remove batch process for the specified importer plugin.
   *
   * @param $plugin_id
   * @return null|RedirectResponse
   */
  public function triggerImporterItemRemoval($plugin_id) {
    $redirect_url = Url::fromRoute('import_api.admin_config_importers');

    /** @var ImporterPluginBase $importer */
    $importer = $this->importerManager
      ->createInstance($plugin_id);

    // Create a remove batch instance for the specified importer.
    $this->importerRemoveService
      ->createRemoveBatchFor($importer);

    // Start the batch process.
    return $this->importerRemoveService
      ->removeBatchProcess($redirect_url);
  }
}
