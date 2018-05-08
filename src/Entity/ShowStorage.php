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
    $values['show_id'] = $results['id'];
  
    foreach ($results['attributes'] as $key => $value) {
      $values[$key] = $value;
    }
    return $values;
  }
  
  
}
