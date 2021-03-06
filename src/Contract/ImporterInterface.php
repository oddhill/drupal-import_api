<?php

namespace Drupal\import_api\Contract;

use Drupal\import_api\BatchStatus;

interface ImporterInterface {

  /**
   * Handle the batch of data.
   *
   * @param array $data
   *   The data received from the fetch request.
   * @param BatchStatus $batch_status
   *   The current batch status as an object that you can easily modify
   * @param $context
   *   The current batch context. Only modify this if you know what you're
   *   doing since the Import API will modify the context after each
   *   batch run and might override your values.
   *
   * @return number
   */
  public function batch($data, BatchStatus $batch_status, &$context);

  /**
   * Fetch data required for the batch process.
   *
   * @param array $context
   *   The current batch context.
   *
   * @return mixed
   */
  public function fetch($context);

  /**
   * Perform something before starting the main batch operation.
   *
   * @param array $context
   *   The current batch context.
   *
   * @return array
   *   Return an array of data to store in the batch context. This data can be
   *   accessed when performing the main batch operation. Useful when you
   *   need to pre-fetch data.
   */
  public function preBatch($context);

  /**
   * Perform something after the main batch operation has completed. Useful if
   * you need to implement custom logging or other actions.
   *
   * @param array $context
   *   The current batch context.
   */
  public function postBatch($context);

  /**
   * Should return the total number of items to be imported.
   *
   * @param array $data
   *   The data to get a total from.
   *
   * @return int
   *   The number of total items to import.
   */
  public function getTotal($data);

  /**
   * Query the items to remove and return an array with the loaded items.
   *
   * If you want to split the batch into multiple steps only run a sub-set
   * of items with each query and this method will be run on each batch
   * iteration.
   *
   * @param array $context
   *   The current batch context.
   *
   * @return array
   *   Should return an array of queried items.
   */
  public function removeQuery(&$context);

  /**
   * Perform the remove action on the items fetched from the remove query.
   *
   * @param array $data
   *   Items to remove.
   * @param $context
   *   The current batch context.
   */
  public function removeBatch($data, $context);

  /**
   * Should return the total number of items to remove.
   *
   * @return int
   */
  public function getRemoveBatchTotal();
}
