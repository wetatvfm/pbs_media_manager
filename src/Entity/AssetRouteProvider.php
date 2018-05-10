<?php

namespace Drupal\pbs_media_manager\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for assets.
 */
class AssetRouteProvider implements EntityRouteProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();
    $route = (new Route('/video/{pbs_mm_asset_id}'));
    $route_collection->add('entity.asset.canonical', $route);
    
    return $route_collection;
  }
}