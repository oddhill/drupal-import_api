<?php

namespace Drupal\import_api\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\import_api\Contract\ImporterInterface;

abstract class ImporterPluginBase extends PluginBase implements ImporterInterface {

  /**
   * {@inheritdoc}
   */
  public function deserialize($format, $data) {
    // The deserialize method is optional.
  }
}
