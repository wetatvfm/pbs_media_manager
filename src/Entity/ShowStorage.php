<?php

namespace Drupal\pbs_media_manager\Entity;

use GuzzleHttp\Client;
use Drupal\Core\Entity\ContentEntityNullStorage;

/**
 *
 */
class ShowStorage extends ContentEntityNullStorage {
  
  /**
   * The default info for building our API request
   * @var string
   */
  private $endpoint = "https://media.services.pbs.org/api/v1/shows/";
  private $client_id = "YOUR KEY HERE";
  private $client_secret ="YOUR SECRET HERE";
  
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
    $client = new Client();
    $response = $client->get($this->endpoint($id), ['auth' =>
      [$this->client_id, $this->client_secret]]);
    $data = json_decode($response->getBody(), TRUE);
    return $this->mapValues($data);
  }
  
  /**
   *
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
