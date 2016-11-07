<?php

/**
 * @file
 * Contains function related to handling batch operations.
 */

/**
 * This function is just a middleman to pass the data to the batch method on
 * the importer service.
 *
 * @param string $importer_id
 * @param array $context
 */
function import_api_handle_batch($importer_id, &$context) {
  /** @var \Drupal\import_api\ImporterService $importer_service */
  $importer_service = \Drupal::service('import_api.importer_service');
  $importer_service->batch($importer_id, $context);
}

function import_api_batch_1($string, $array, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $count = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->count()
      ->execute();

    \Drupal::logger('import_api')->info("Count: {$count}");

    $context['sandbox']['current_id'] = 0;
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['total'] = $count;
  }

  $array = serialize($array);

  \Drupal::logger('import_api')->info("String: {$string}");
  \Drupal::logger('import_api')->info("Array: {$array}");

  \Drupal::logger('import_api')->info("Staring new batch");

  $node_ids = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->getQuery()
    ->condition('nid', $context['sandbox']['current_id'], '>')
    ->sort('nid')
    ->range(0, 2)
    ->execute();

  /** @var \Drupal\node\NodeInterface[] $nodes */
  $nodes = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadMultiple($node_ids);

  sleep(1);

  foreach ($nodes as $node) {
    $context['message'] = new \Drupal\Core\StringTranslation\TranslatableMarkup('Now loading %title', [
      '%title' => $node->getTitle(),
    ]);

    $context['sandbox']['current_id'] = $node->id();
    $context['results'][] = $node->id();
    $context['sandbox']['progress']++;

    \Drupal::logger('import_api')->info("Processing: {$node->getTitle()}");
  }

  $progress = $context['sandbox']['progress'] / $context['sandbox']['total'];

  \Drupal::logger('import_api')->info("Current progress: {$progress}");

  if ($context['sandbox']['progress'] != $context['sandbox']['total']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['total'];
  }
}

function import_api_batch_finished($success, $result, $operations) {
  if ($success) {
    $message = new \Drupal\Core\StringTranslation\TranslatableMarkup('Importing done');
  }
  else {
    $message = new \Drupal\Core\StringTranslation\TranslatableMarkup('Finished with an error.');
  }

  drupal_set_message($message);
}
