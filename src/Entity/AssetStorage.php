<?php

namespace Drupal\pbs_media_manager\Entity;

use Drupal\pbs_media_manager\Client\APIConnect;
use Drupal\Core\Entity\ContentEntityNullStorage;

class AssetStorage extends ContentEntityNullStorage{
  /**
   * {@inheritdoc}
   */
  public function load($id, $default = NULL) {
    $asset = $this->getAsset($id);
    return isset($asset) ? $asset : $default;
  }
  
  /**
   *
   */
  public function getAsset($id) {
    $params = [
      'platform-slug' => 'partnerplayer',
    ];
    $connect = new APIConnect();
    $client = $connect->connect();
    $response = $client->get_asset($id, FALSE, $params);
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