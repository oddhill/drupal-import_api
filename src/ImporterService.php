<?php

namespace Drupal\import_api;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Drupal\import_api\ValueObject\FetchResponse;
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
        '%label' => $plugin_definition['admin_label'],
      ]),
      'operations' => [
        [[$this, 'batch'], [$plugin_definition['id']]],
      ],
      'finished' => 'import_api_batch_finished',
      'file' => drupal_get_path('module', 'import_api') . '/import_api.module',
    ]);

    // return batch_process(Url::fromRoute('import_api.admin_config_importers'));
  }

  /**
   * @param $importer_id
   * @param $context
   */
  public function batch($importer_id, &$context) {
    $importer = $this->getImporterInstance($importer_id);

    /** @var FetchResponse $response */
    $response = $importer->fetch($this->getPreviousFetchResponse($context));

    // Set some default values to the sandbox if this is the first batch.
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current'] = $response->getCurrent();
      $context['sandbox']['total'] = $response->getTotal();
    }

    $data = $response->getData();
    $plugin_definition = $importer->getPluginDefinition();

    // If a format has been specified then deserialize the received data with
    // the specified format.
    if (isset($plugin_definition['format'])) {
      $data = $this->deserializeData($data, $plugin_definition['format']);
    }

    // Call the importers batch method with the data and the current context
    // to let the importer process the data for the current batch.
    $importer->batch($data, $context['sandbox']['progress']);

    // Update the progress after each importer batch has run.
    if ($context['sandbox']['progress'] !== $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['total'];
    }
  }

  private function getPreviousFetchResponse($context) {
    return isset($context['sandbox']['fetch_response'])
      ? $context['sandbox']['fetch_response']
      : null;
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
