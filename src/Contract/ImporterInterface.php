<?php

namespace Drupal\import_api\Contract;

interface ImporterInterface {

  /**
   * Handle the batch of data.
   *
   * @param $data array
   *   The data received from the fetch request.
   *
   * @param $context array
   *   The batch context.
   *
   * @see https://api.drupal.org/api/drupal/core%21includes%21form.inc/group/batch/8.2.x
   *
   * @return void
   */
  public function batch($data, &$context);

  /**
   * Fetch data required for the batch process.
   *
   * @return array
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
