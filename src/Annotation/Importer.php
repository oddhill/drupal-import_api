<?php

namespace Drupal\import_api\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Describes an Importer annotation object.
 *
 * @ingroup import_api
 *
 * @Annotation
 */
class Importer extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable label displayed in the administrative interface.
   *
   * @ingroup plugin_translatable
   *
   * @var Translation
   */
  public $label = '';

  /**
   * The category in the Admin UI where this importer will be listed.
   *
   * @ingroup plugin_translatable
   *
   * @var Translation
   */
  public $category = '';

  /**
   * The data format received by this importer when loading data. If a format
   * has been defined here this format will be used when calling the
   * unserializer on the fetched data.
   *
   * @var null
   *
   * @todo Evaluate if this is needed? Most times a library like guzzle will
   *       probably be used and will do the de-serialization.
   */
  public $format = null;

  /**
   * An associative array containing the optional key:
   *   - time: (optional) How much time Drupal cron should spend on calling
   *     this worker in seconds. Defaults to 15.
   *
   * @var array (optional)
   */
  public $cron;
}
