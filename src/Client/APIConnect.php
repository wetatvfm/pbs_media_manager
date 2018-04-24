<?php
namespace Drupal\pbs_media_manager\Client;

use Drupal\pbs_media_manager\Client\PBS_Media_Manager_API_Client as PBSClient;

class APIConnect {
  
  private $endpoint = "https://media.services.pbs.org/api/v1/";
  
  public function connect() {
    //Immutable Config (Read Only)
    $config = \Drupal::config('pbs_media_manager.settings');
    $key = $config->get('key');
    $secret = $config->get('secret');
    return new PBSClient($key, $secret, $this->endpoint);
  }
  
}