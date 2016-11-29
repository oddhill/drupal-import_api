<?php

namespace Drupal\import_api;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class ImporterHelpers {

  /**
   * @var EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * ImporterHelpers constructor.
   * @param EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function updateExistingOrCreateNew($entity_type, $uuid, $values) {
    $entity_storage = $this->entityTypeManager->getStorage($entity_type);


    $entities = $entity_storage->loadByProperties([
      'uuid' => $uuid,
    ]);

    if (!count($entities)) {
      $entity = $entity_storage
        ->create($values)
        ->save();
    } else {
      //$entity = $entities[0]->
    }

    return $entity;
  }

}
