<?php

namespace Drupal\import_api\Contract;

use Drupal\import_api\BatchStatus;

interface ImporterInterface {

  /**
   * Handle the batch of data.
   *
   * @param $data array
   *   The data received from the fetch request.
   *
   * @return number
   */
  public function batch($data, BatchStatus $batch_status);

  /**
   * Fetch data required for the batch process.
   *
   * @return mixed
   */
  public function fetch();

  /**
   * Deserialize the fetched data.
   *
   * @param $format string
   *   The format of the data to deserialize.
   *
   * @param $data mixed
   *   The data to deserialize.
   *
   * @return mixed
   */
  public function deserialize($format, $data);
}
