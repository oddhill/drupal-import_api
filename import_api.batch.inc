<?php

/**
 * @file
 * Contains function related to handling batch operations.
 */

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * This function is just a middleman to pass the data to the batch method on
 * the importer service.
 *
 * @param string $method
 *   Name of the method to call on the importer service.
 * @param string $importer_id
 *   Machine name of the importer.
 * @param array $context
 *   The current batch context.
 */
function import_api_handle_batch($method, $importer_id, &$context) {
  /** @var \Drupal\import_api\ImporterService $importer_service */
  $importer_service = \Drupal::service('import_api.importer_service');
  $importer_service->{$method}($importer_id, $context);
}

/**
 * Handle a finished batch.
 *
 * @param $success
 *   If the batch operations were successful.
 * @param $result
 *   The result from the batch operations.
 * @param $operations
 *
 */
function import_api_batch_finished($success, $result, $operations) {
  /** @var \Drupal\import_api\ImporterManager $importer_manager */
  $importer_manager = \Drupal::service('plugin.manager.importer');

  /** @var \Drupal\import_api\Plugin\ImporterPluginBase $importer */
  $importer = $importer_manager->createInstance($result['importer_id']);

  if ($success) {
    drupal_set_message(new TranslatableMarkup('Importing done'));

    \Drupal::logger('import_api')->info('Finished importing of @label with the following results: <pre>@results</pre>', [
      '@label' => $importer->getLabel(),
      '@results' => print_r($result, TRUE),
    ]);
  }
  else {
    drupal_set_message(new TranslatableMarkup('Importing done'), 'error');

    \Drupal::logger('import_api')->error('Importing failed for @label.', [
      '@label' => $importer->getLabel(),
    ]);
  }
}
