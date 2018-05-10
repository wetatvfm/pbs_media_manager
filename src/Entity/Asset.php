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
 *    "route_provider" = {
 *       "html" = "Drupal\pbs_media_manager\Entity\AssetRouteProvider",
 *     },
 *   },
 *   base_table = NULL,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title"
 *   },
 *   links = {
 *     "canonical" = "/video/{pbs_mm_asset_id}",
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
  
  public function content($pbs_mm_asset_id) {
    $asset = $this->load($pbs_mm_asset_id);
    if ($asset) {
      $entity['#theme'] = 'pbs_mm_asset';
      $entity['#asset'] = $asset;
  
      return $entity;
    }
    else {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

  }
}