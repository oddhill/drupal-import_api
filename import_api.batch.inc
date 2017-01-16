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
 */
function import_api_batch_finished($success, $result) {
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
    drupal_set_message(new TranslatableMarkup('There was an error during import'), 'error');

    \Drupal::logger('import_api')->error('Importing failed for @label.', [
      '@label' => $importer->getLabel(),
    ]);
  }

  $importer->resetQueuedAt();
}

/**
 * Handle the batching to remove items for an importer.
 *
 * This function is just a middle hand to instantiate the importer and call
 * the remove batch method on the importer instance.
 *
 * @param string $importer_id
 *   Id if the importer.
 * @param $context
 *   The current batch context.
 */
function import_api_handle_remove_batch($importer_id, &$context) {
  /** @var \Drupal\import_api\ImporterRemoveService $importer_remove_service */
  $importer_remove_service = \Drupal::service('import_api.importer_remove_service');
  $importer_remove_service->handleRemoveBatch($importer_id, $context);
}

/**
 * Handle a finished remove batch.
 */
function import_api_remove_batch_finished($success, $result) {
  /** @var \Drupal\import_api\ImporterManager $importer_manager */
  $importer_manager = \Drupal::service('plugin.manager.importer');

  /** @var \Drupal\import_api\Plugin\ImporterPluginBase $importer */
  $importer = $importer_manager->createInstance($result['importer_id']);

  if ($success) {
    drupal_set_message(new TranslatableMarkup('Finished removing items for the <em>@label</em> importer.', [
      '@label' => $importer->getLabel(),
    ]));

    \Drupal::logger('import_api')->info('Finished removal of imported items for @label with the following results: <pre>@results</pre>', [
      '@label' => $importer->getLabel(),
      '@results' => print_r($result, TRUE),
    ]);
  }
  else {
    drupal_set_message(new TranslatableMarkup('Failed removing imported items for <em>@label</em>.', [
      '@label' => $importer->getLabel(),
    ]), 'error');

    \Drupal::logger('import_api')->error('Failed removing imported items for <em>@label</em>.', [
      '@label' => $importer->getLabel(),
    ]);
  }
}
