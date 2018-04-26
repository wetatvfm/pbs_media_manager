<?php

namespace Drupal\pbs_media_manager\Entity;

use Drupal\pbs_media_manager\Client\APIConnect;
use Drupal\Core\Entity\ContentEntityNullStorage;

/**
 *
 */
class ShowStorage extends ContentEntityNullStorage {

  /**
   * {@inheritdoc}
   */
  public function load($id, $default = NULL) {
    $show = $this->getShow($id);
    return isset($show) ? $show : $default;
  }
  
  /**
   *
   */
  public function getShow($id) {
    $connect = new APIConnect();
    $client = $connect->connect();
    $response = $client->get_show($id);
    return $this->mapValues($response);
  }
  
  /**
   *
   */
  public function mapValues($data) {
    $results = $data['data'];
    return [
      'id' => $results['id'],
      'slug' => $results['attributes']['slug'],
      'title' => $results['attributes']['title'],
      'description_short' => $results['attributes']['description_short'],
      'description_long' => $results['attributes']['description_long'],
    ];
  }
  
}
