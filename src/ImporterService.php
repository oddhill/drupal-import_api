<?php

namespace Drupal\import_api;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Drupal\import_api\ValueObject\FetchResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Serializer\Serializer;

class ImporterService {

  /**
   * @var Serializer
   */
  private $serializer;

  /**
   * @var ImporterManager
   */
  private $importerManager;

  /**
   * ImporterService constructor.
   *
   * @param ImporterManager $importerManager
   * @param Serializer $serializer
   */
  public function __construct(
    ImporterManager $importerManager,
    Serializer $serializer
  ) {
    $this->importerManager = $importerManager;
    $this->serializer = $serializer;
  }

  /**
   * Create a batch for the specified importer.
   *
   * @param ImporterPluginBase $importer
   */
  public function createBatchFor(ImporterPluginBase $importer) {
    $plugin_definition = $importer->getPluginDefinition();

    batch_set([
      'title' => new TranslatableMarkup('Importing: %label', [
        '%label' => $plugin_definition['label'],
      ]),
      'operations' => [
        ['import_api_handle_batch', [$plugin_definition['id']]],
      ],
      'finished' => 'import_api_batch_finished',
      'file' => drupal_get_path('module', 'import_api') . '/import_api.batch.inc',
    ]);
  }

  /**
   * Start processing the batch.
   *
   * @param Url $url
   *   The redirect URL for the batch process.
   *
   * @return null|RedirectResponse
   */
  public function batchProcess(Url $url) {
    return batch_process($url);
  }

  /**
   * @param $importer_id
   * @param $context
   */
  public function batch($importer_id, &$context) {
    $importer = $this->getImporterInstance($importer_id);
    $plugin_definition = $importer->getPluginDefinition();

    /** @var FetchResponse $response */
    $data = $importer->fetch();

    // If a format has been specified then deserialize the received data with
    // the specified format.
    if (isset($plugin_definition['format'])) {
      $data = $this->deserializeData($data, $plugin_definition['format']);
    }

    $total = $importer->getTotal($data);

    $batch_status = !isset($context['sandbox']['progress'])
      ? $this->createInitialBatchStatus($total)
      : $this->createBatchStatusFromContext($context);

    // Call the importers batch method with the data and the current batch
    // status.
    $importer->batch($data, $batch_status);

    \Drupal::logger('import_api')->info('<pre>'.print_r($batch_status, TRUE).'</pre>');

    $this->applyBatchStatusToContext($batch_status, $context);

    // Update the progress after each importer batch has run.
    if ($context['sandbox']['progress'] !== $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['total'];
    }
  }

  /**
   * Create a batch status object for the initial batch.
   *
   * @param int $total
   *   The total number of items in the batch process.
   *
   * @return BatchStatus
   */
  private function createInitialBatchStatus($total) {
    return new BatchStatus(0, NULL, $total);
  }

  /**
   * Create a batch status object fom the current batch process context.
   *
   * @param array $context
   *   The current batch context.
   *
   * @return BatchStatus
   */
  private function createBatchStatusFromContext($context) {
    return new BatchStatus(
      $context['sandbox']['progress'],
      $context['sandbox']['current'],
      $context['sandbox']['total'],
      $context['results']
    );
  }

  /**
   * Apply the current batch status to the batch process context.
   *
   * @param BatchStatus $batch_status
   *   The current batch status.
   * @param $context
   *   The current batch process context.
   */
  private function applyBatchStatusToContext(BatchStatus $batch_status, &$context) {
    $context['sandbox']['progress'] = $batch_status->getProgress();
    $context['sandbox']['current'] = $batch_status->getCurrent();
    $context['sandbox']['total'] = $batch_status->getTotal();
    $context['message'] = $batch_status->getMessage();
  }

  /**
   * Deserialize the supplied data if the format is supported.
   *
   * @param string $data
   *   The data to deserialize.
   * @param string $format
   *   The format to deserialize from.
   *
   * @return mixed
   */
  private function deserializeData($data, $format) {
    if (!$this->serializer->supportsDecoding($format)) {
      return $data;
    }

    return $this->serializer
      ->decode($data, $format);
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
