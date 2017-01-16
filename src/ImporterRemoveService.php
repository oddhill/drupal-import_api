<?php

namespace Drupal\import_api;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ImporterRemoveService {

  /**
   * @var ImporterManager
   */
  private $importerManager;

  /**
   * ImporterRemoveService constructor.
   *
   * @param ImporterManager $importerManager
   */
  public function __construct(ImporterManager $importerManager) {
    $this->importerManager = $importerManager;
  }

  /**
   * Create a batch for the specified importer.
   *
   * @param ImporterPluginBase $importer
   */
  public function createRemoveBatchFor(ImporterPluginBase $importer) {
    $plugin_definition = $importer->getPluginDefinition();

    batch_set([
      'title' => new TranslatableMarkup('Removing items imported by: %label', [
        '%label' => $plugin_definition['label'],
      ]),
      'operations' => [
        ['import_api_handle_remove_batch', [$plugin_definition['id']]],
      ],
      'finished' => 'import_api_remove_batch_finished',
      'file' => drupal_get_path('module', 'import_api') . '/import_api.batch.inc',
    ]);
  }

  /**
   * Handle the current iteration of the remove batch.
   *
   * @param string $importer_id
   *   The machine name of the importer.
   * @param array $context
   *   The current batch context.
   */
  public function handleRemoveBatch($importer_id, &$context) {
    $importer = $this->getImporterInstance($importer_id);

    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max'] = $importer->getRemoveBatchTotal();

      // Add the importer id to that we can use it when the removal is done.
      $context['results']['importer_id'] = $importer_id;
    }

    $items_to_remove = $importer->removeQuery($context);

    $importer->removeBatch($items_to_remove, $context);

    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Start processing the removal batch.
   *
   * @param Url $url
   *   The redirect URL for the batch process.
   *
   * @return null|RedirectResponse
   */
  public function removeBatchProcess(Url $url) {
    return batch_process($url);
  }

  /**
   * Get an instance of the importer plugin with the specified id.
   *
   * @param string $importer_id
   *   The ID of the importer to get an instance for.
   *
   * @return ImporterPluginBase
   */
  private function getImporterInstance($importer_id) {
    return $this->importerManager->createInstance($importer_id);
  }
}
