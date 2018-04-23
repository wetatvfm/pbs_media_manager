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
    $entity = $this->load($id);
    return [
      '#theme' => 'show',
      '#id' => $entity['id'],
      '#title' => $entity['title'],
      '#slug' => $entity['slug'],
      '#description_short' => $entity['description_short'],
      '#description_long' => $entity['description_long'],
    ];
  }

}
