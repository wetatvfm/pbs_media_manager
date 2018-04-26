<?php

namespace Drupal\pbs_media_manager\Entity;

use Drupal\pbs_media_manager\Client\APIConnect;
use Drupal\Core\Entity\ContentEntityNullStorage;

class AssetStorage extends ContentEntityNullStorage{
  /**
   * {@inheritdoc}
   */
  public function load($id, $default = NULL) {
    $show = $this->getAsset($id);
    return isset($show) ? $show : $default;
  }
  
  /**
   *
   */
  public function getAsset($id) {
    $connect = new APIConnect();
    $client = $connect->connect();
    $response = $client->get_asset($id);
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
      'player' => $results['attributes']['player_code'],
      'description' => $results['attributes']['description_long'],
    ];
  }
}