<?php

namespace Drupal\pbs_media_manager\Entity;


use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for assets.
 */
class ShowRouteProvider implements EntityRouteProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();
    $route = (new Route('/show/{pbs_mm_show_id}'))
      ->addDefaults([
        '_controller' => '\Drupal\pbs_media_manager\Entity\Show::content',
      ])
      ->setRequirement('_permission', 'access content');
    $route_collection->add('entity.show.canonical', $route);
    
    return $route_collection;
  }
}