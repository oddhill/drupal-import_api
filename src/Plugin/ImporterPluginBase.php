<?php

namespace Drupal\import_api\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\import_api\Contract\ImporterInterface;

abstract class ImporterPluginBase extends PluginBase implements ConfigurablePluginInterface, ImporterInterface {

  /**
   * Configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * @var KeyValueStoreInterface
   */
  protected $keyValueStore;

  /**
   * The plugin label.
   *
   * @return TranslatableMarkup
   */
  public function getLabel() {
    return $this->getPluginDefinition()['label'];
  }

  /**
   * Set the last time this importer was queued by a queue worker.
   *
   * Defaults to the current request time if no value is supplied.
   *
   * @param int $time
   *   The last run time as a unix time stamp.
   *
   * @return $this
   */
  public function setQueuedAt($time = REQUEST_TIME) {
    $this->getKeyValueStore()
      ->set($this->formatKeyName('queued'), (int) $time);

    return $this;
  }

  /**
   * Reset the queued at timestamp to 0.
   *
   * @return $this
   */
  public function resetQueuedAt() {
    $this->getKeyValueStore()
      ->set($this->formatKeyName('queued'), 0);

    return $this;
  }

  /**
   * Get the queued at timestamp.
   *
   * Returns 0 when a queue time has not been set.
   *
   * @return int
   */
  public function getQueuedAt() {
    return (int) $this->getKeyValueStore()
      ->get($this->formatKeyName('queued'), 0);
  }

  /**
   * Get the lat time this importer was run at as a unix timestamp.
   *
   * Will return a 0 value if the importer has never been run.
   *
   * @return int
   *   The last time this importer was run.
   */
  public function getLastRunAt() {
    return (int) $this->getKeyValueStore()
      ->get($this->formatKeyName('last_run'), 0);
  }

  /**
   * Set the lat time this importer was run. The supplied value should be a unix
   * timestamp.
   *
   * Defaults to the current request time if no value is supplied.
   *
   * @param int $time
   *   The last run time as a unix time stamp.
   *
   * @return $this
   */
  public function setLastRunAt($time = REQUEST_TIME) {
    $this->getKeyValueStore()
      ->set($this->formatKeyName('last_run'), (int) $time);

    return $this;
  }

  /**
   * Get the cron interval in seconds.
   *
   * @return int
   */
  public function getCronIntervalTime() {
    return (int) $this->getPluginDefinition()['cron'] * 60;
  }

  /**
   * Check if this importer should be queued.
   *
   * @return bool
   *   A boolean value.
   */
  public function shouldBeQueued() {
    $last_run = $this->getLastRunAt();

    if ($last_run === 0) {
      return TRUE;
    }

    $cron_interval = $this->getCronIntervalTime();

    return ($last_run + $cron_interval) < REQUEST_TIME;
  }

  /**
   * Get the Import API key value store.
   *
   * @return KeyValueStoreInterface
   */
  public function getKeyValueStore() {
    if (!$this->keyValueStore) {
      $this->keyValueStore = \Drupal::keyValue('import_api');
    }

    return $this->keyValueStore;
  }

  /**
   * {@inheritdoc}
   */
  public function deserialize($format, $data) {
    // The deserialize method is optional.
  }

  /**
   * {@inheritdoc}
   */
  public function preBatch($context) {
    // Pre-batch is optional.
  }

  /**
   * {@inheritdoc}
   */
  public function postBatch($context) {
    // Post-batch is optional.
  }

  /**
   * Gets this plugin's configuration.
   *
   * @return array
   *   An array of this plugin's configuration.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Sets the configuration for this plugin instance.
   *
   * @param array $configuration
   *   An associative array containing the plugin's configuration.
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Gets default configuration for this plugin.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  public function defaultConfiguration() {
    return [];
  }

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
  public function removeQuery(&$context) {
    return [];
  }

  /**
   * Perform the remove action on the items fetched from the remove query.
   *
   * @param array $data
   *   Items to remove.
   * @param $context
   *   The current batch context.
   */
  public function removeBatch($data, $context) {
    // This method is optional.
  }

  /**
   * Should return the total number of items to remove.
   *
   * @return int
   */
  public function getRemoveBatchTotal() {
    return 0;
  }

  /**
   * Calculates dependencies for the configured plugin.
   *
   * Dependencies are saved in the plugin's configuration entity and are used to
   * determine configuration synchronization order. For example, if the plugin
   * integrates with specific user roles, this method should return an array of
   * dependencies listing the specified roles.
   *
   * @return array
   *   An array of dependencies grouped by type (config, content, module,
   *   theme). For example:
   * @code
   *   array(
   *     'config' => array('user.role.anonymous', 'user.role.authenticated'),
   *     'content' => array('node:article:f0a189e6-55fb-47fb-8005-5bef81c44d6d'),
   *     'module' => array('node', 'user'),
   *     'theme' => array('seven'),
   *   );
   * @endcode
   *
   * @see \Drupal\Core\Config\Entity\ConfigDependencyManager
   * @see \Drupal\Core\Entity\EntityInterface::getConfigDependencyName()
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Format a name for the key/value storage with the supplied suffix.
   *
   * @param $suffix
   * @return string
   */
  protected function formatKeyName($suffix) {
    return implode('.', [$this->getPluginId(), $suffix]);
  }

}
