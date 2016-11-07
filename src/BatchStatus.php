<?php

namespace Drupal\import_api;

/**
 * Describes the current status of the ongoing batch.
 */
class BatchStatus {

  /**
   * The total number of items to import.
   *
   * @var number
   */
  private $total;

  /**
   * The last item handled in the batch process.
   *
   * @var mixed|null
   */
  private $current;

  /**
   * Array of batch results.
   *
   * @var array
   */
  private $result;

  /**
   * Progress of the batch operation.
   *
   * @var int
   */
  private $progress;

  /**
   * Message to display to the user.
   *
   * @var null
   */
  private $message = NULL;

  /**
   * BatchStatus constructor.
   *
   * @param int $progress
   *   The current progress of the batch operation.
   * @param mixed $current
   *   The last item imported by the
   * @param number $total
   *   The total number of items to process in the batch operation.
   * @param array $result
   *   The result of the batch operation.
   */
  public function __construct($progress = 0, $current = NULL, $total, array $result = []) {
    $this->progress = $progress;
    $this->total = $total;
    $this->current = $current;
    $this->result = $result;
  }

  /**
   * Get the total number of items to process.
   *
   * @return number
   */
  public function getTotal() {
    return $this->total;
  }

  /**
   * Get the last processed item.
   *
   * @return mixed|null
   */
  public function getCurrent() {
    return $this->current;
  }

  /**
   * Set the currently processed item.
   *
   * @param $current
   * @return $this
   */
  public function setCurrent($current) {
    $this->current = $current;
    return $this;
  }

  /**
   * Add an entry to the results array.
   *
   * @param $result
   * @return $this
   */
  public function addResult($result) {
    $this->result[] = $result;
    return $this;
  }

  /**
   * Get the current result.
   *
   * @return array
   */
  public function getResult() {
    return $this->result;
  }

  /**
   * Get the message.
   *
   * @return null
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Set the message.
   *
   * @param mixed $message
   * @return $this
   */
  public function setMessage($message) {
    $this->message = $message;
    return $this;
  }

  /**
   * Increment the progress of the batch operation.
   *
   * @return $this
   */
  public function incrementProgress() {
    $this->progress = $this->progress++;
    return $this;
  }

  /**
   * Get the current progress of the batch operation.
   *
   * @return int
   */
  public function getProgress() {
    return $this->progress;
  }
}
