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
 *    "route_provider" = {
 *       "html" = "Drupal\pbs_media_manager\Entity\ShowRouteProvider",
 *     },
 *   },
 *   base_table = NULL,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title"
 *   },
 *   links = {
 *     "canonical" = "/show/{pbs_mm_show_id}",
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

  public function content($pbs_mm_show_id) {
    $show = $this->load($pbs_mm_show_id);
    if ($show) {
      $entity['#theme'] = 'pbs_mm_show';
      $entity['#show'] = $show;
  
      return $entity;
    }
    else {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
   
  }
  
  /**
   * Returns a page title.
   */
  public function getTitle($pbs_mm_show_id) {
    $show = $this->load($pbs_mm_show_id);
    if ($show) {
      return $show['title'];
    }
   
  }

}
