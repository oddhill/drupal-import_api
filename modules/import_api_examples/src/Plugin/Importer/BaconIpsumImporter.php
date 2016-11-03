<?php

namespace Drupal\import_api_examples;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Drupal\import_api\ValueObject\FetchResponse;
use Drupal\node\Entity\Node;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Importer(
 *   id = "import_api_examples_bacon_ipsum_importer"
 *   label = @Translation("Array importer")
 *   category = @Translation("Import API examples")
 *   format = "json"
 * )
 */
class BaconIpsumImporter extends ImporterPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * BaconIpsumImporter constructor.
   *
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param string $configuration
   * @param mixed $plugin_id
   * @param $plugin_definition
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $container->get('entity_type.manager'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

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
  public function batch($data, &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($data);
    }

    foreach ($data as $paragraph) {
      $title = preg_split('/[\s,]+/', $paragraph, rand(3, 5));

      $node = Node::create([
        'type' => 'bacon_ipsum',
        'title' => $title,
        'body' => $paragraph,
      ]);

      $node->save();
    }
  }

  /**
   * Fetch data required for the batch process.
   *
   * @return array
   */
  public function fetch() {
    $client = new Client();

    $response = $client->request('GET', 'https://baconipsum.com/api', [
      'query' => [
        'type' => 'meat-and-filler',
        'paras' => 10,
      ],
    ]);

    $data = $response->getBody()->getContents();

    return new FetchResponse();
  }
}
