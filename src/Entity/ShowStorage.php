<?php

namespace Drupal\pbs_media_manager\Entity;

use Drupal\pbs_media_manager\Client\PBS_Media_Manager_API_Client as PBSClient;
use Drupal\pbs_media_manager\Client\APIConnect;
use Drupal\Core\Entity\ContentEntityNullStorage;

/**
 *
 */
class ShowStorage extends ContentEntityNullStorage {
  
  /**
   * The default info for building our API request
   * @var string
   */
  //private $endpoint = "https://media.services.pbs.org/api/v1/shows/";
  private $endpoint = "https://media.services.pbs.org/api/v1/";
  
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
   * No longer using this, but leaving it in case we change our minds.
   */
  public function endpoint($id) {
    //$params = "&format=json&field_list=id,name,real_name,deck,description,
    //origin,image,api_detail_url";
    return $this->endpoint . $id;
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
