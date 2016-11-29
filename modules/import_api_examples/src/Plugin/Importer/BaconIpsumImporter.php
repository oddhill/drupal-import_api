<?php

namespace Drupal\import_api_examples\Plugin\Importer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\import_api\BatchStatus;
use Drupal\import_api\Plugin\ImporterPluginBase;
use Drupal\node\Entity\Node;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Importer(
 *   id = "import_api_examples_bacon_ipsum_importer",
 *   label = @Translation("Bacon Ipsum Importer"),
 *   category = @Translation("Import API examples"),
 *   format = "json",
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
   * {@inheritdoc}
   */
  public function batch($data, BatchStatus $batch_status, &$context) {
    foreach ($data as $index => $paragraph) {
      $title = 'Bacon Ipsum';

      $node = Node::create([
        'type' => 'bacon_ipsum',
        'title' => $title,
        'body' => $paragraph,
      ]);

      $node->save();

      $batch_status
        ->setCurrent($index)
        ->addResult($index)
        ->setMessage(new TranslatableMarkup('Importing: @title', [
          '@title' => $title,
        ]))
        ->incrementProgress();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($context) {
    $client = new Client();

    $response = $client->request('GET', 'https://baconipsum.com/api', [
      'query' => [
        'type' => 'meat-and-filler',
        'paras' => 10,
      ],
    ]);

    return $response->getBody()->getContents();
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal($data) {
    return count($data);
  }
}
