<?php

namespace Drupal\pbs_media_manager\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the Asset entity.
 *
 * @ingroup pbs_media_manager
 *
 * @ContentEntityType(
 *   id = "asset",
 *   label = @Translation("Asset"),
 *   handlers = {
 *    "storage" = "Drupal\pbs_media_manager\Entity\AssetStorage",
 *   },
 *   base_table = NULL,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title"
 *   }
 * )
 */

class Asset extends ContentEntityBase {

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
      '#theme' => 'asset',
      '#id' => $entity['id'],
      '#title' => $entity['title'],
      '#slug' => $entity['slug'],
      '#player' => $entity['player'],
      '#description' => $entity['description'],
    ];
  }
}