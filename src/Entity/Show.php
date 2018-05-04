<?php

namespace Drupal\pbs_media_manager\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the Show entity.
 *
 * @ingroup pbs_media_manager
 *
 * @ContentEntityType(
 *   id = "show",
 *   label = @Translation("Show"),
 *   handlers = {
 *    "storage" = "Drupal\pbs_media_manager\Entity\ShowStorage",
 *   },
 *   base_table = NULL,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title"
 *   }
 * )
 */
class Show extends ContentEntityBase {

  use EntityChangedTrait;

  public function __construct(array $values = []) {
    // Set initial values.
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  public function content($id) {
    $entity['#theme'] = 'show';
    $entity['#show'] = $this->load($id);
  
    return $entity;
  }

}
