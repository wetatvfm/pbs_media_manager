<?php
namespace Drupal\pbs_media_manager\Client;

use Drupal\pbs_media_manager\Client\PBS_Media_Manager_API_Client as PBSClient;

class APIConnect {
  
  private $endpoint = "https://media.services.pbs.org/api/v1/";
  protected $key;
  protected $secret;
  
  public function __construct() {
    $config = \Drupal::config('pbs_media_manager.settings');
    $this->key = $config->get('key');
    $this->secret = $config->get('secret');
  }
  
  public function connect() {
    return new PBSClient($this->key, $this->secret, $this->endpoint);
  }
  
}