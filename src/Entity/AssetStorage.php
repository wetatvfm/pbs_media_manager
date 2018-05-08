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
    if (isset($response['data'])) {
      return $this->mapValues($response);
    }
    return NULL;
  }
  
  /**
   *
   */
  public function mapValues($data) {
    $results = $data['data'];
    $values = [];
    $values['asset_id'] = $results['id'];
    
    foreach ($results['attributes'] as $key => $value) {
      $values[$key] = $value;
    }
    return $values;
  }
}