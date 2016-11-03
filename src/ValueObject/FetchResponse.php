<?php

namespace Drupal\import_api\ValueObject;

class FetchResponse {

  private $data;

  private $total;

  private $current;

  /**
   * FetchResponse constructor.
   *
   * @param $data
   * @param $total
   * @param $current
   */
  public function __construct($data, $total, $current) {
    $this->data = $data;
    $this->total = $total;
    $this->current = $current;
  }

  /**
   * @return mixed
   */
  public function getData() {
    return $this->data;
  }

  /**
   * @return mixed
   */
  public function getTotal() {
    return $this->total;
  }

  /**
   * @return mixed
   */
  public function getCurrent() {
    return $this->current;
  }
}
