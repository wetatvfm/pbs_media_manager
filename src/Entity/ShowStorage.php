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
    // If there's a show, get the latest episodes, too.
    // TODO: Probably should be a separate component/function for flexibility.
    if (isset($show)) {
      $episodes = $this->getLatestEpisodes($id, 5);
      $show['episodes'] = $episodes;
    }
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
    $values = [];
    $values['show_id'] = $results['id'];
  
    foreach ($results['attributes'] as $key => $value) {
      $values[$key] = $value;
    }
    return $values;
  }
  
  /**
   * Uses the Media Manager API to build list of episodes for the Show.
   *
   * @param string $id
   *   The id or slug of the show.
   * @param int $count
   *   The number of episodes to retrieve.
   * @param bool $include_specials
   *   Include specials in the list of returned episodes.
   * @param bool $require_players
   *   Only include results that are available to be viewed.
   * @param bool $include_clips
   *   Include clips if there are not enough episodes retrieved.
   * @param bool $include_previews
   *   Include previews if there aer not enough episodes retrieved.
   *
   * @return array
   *   Returns an array of assets, giving preference to full length episodes.
   */
  public function getLatestEpisodes($id, $count, $include_specials = TRUE,
$require_players = TRUE, $include_clips = FALSE, $include_previews = FALSE) {
    // For performance reasons, limit the number of items queried. But still
    // request enough items to allow for some to be filtered out.  The max
    // allowed by the API is 50.
    $max = 50;
    if ($count * 2 < 50) {
      $max = $count * 2;
    }
    // Set up the default query arguments.
    $queryargs = array(
      'show-slug' => $id,
      'type' => 'full_length',
      'platform-slug' => 'partnerplayer',
      'sort' => '-premiered_on',
      'page-size' => $max,
      'page' => 1,
    );
    
    $connect = new APIConnect();
    $client = $connect->connect();
    $episodes = $client->get_assets($queryargs);
  
    // Episode processing only needs to happen if there are results.
    // The 'data' index only exists in empty results.
    // We can't trust the premiered_on sort for episodes that premiered on
    // the same day.
    if (!array_key_exists('data', $episodes)) {
      usort($episodes, function ($a, $b) {
        if (isset($b['attributes']) && isset($a['attributes'])) {
          // If the episodes premiered on the same day...
          if ($b['attributes']['premiered_on'] ==
            $a['attributes']['premiered_on']) {
            // And if there's an episode ordinal for both episodes...
            if (isset($a['attributes']['episode']['attributes']['ordinal']) &&
              isset($b['attributes']['episode']['attributes']['ordinal'])) {
              // Sort by the ordinal.
              return $b['attributes']['episode']['attributes']['ordinal'] -
                $a['attributes']['episode']['attributes']['ordinal'];
            }
          }
          // Otherwise, just sort by premiered_on.
          return strcmp($b['attributes']['premiered_on'],
            $a['attributes']['premiered_on']);
        }
        else {
          return FALSE;
        }
      });
    
      // Filter out unwanted items, such as specials or items without players.
      if ($require_players || !($include_specials)) {
      
        $filtered_episodes = array();
      
        foreach ($episodes as $episode) {
          $pass = TRUE;
          if ($require_players) {
            if (!($this->checkAvailability($episode))) {
              $pass = FALSE;
            }
          }
          if (!$include_specials) {
            if ($episode['attributes']['episode']['type'] == 'special') {
              $pass = FALSE;
            }
          }
        
          // If all conditions are met, add to the results.
          if ($pass) {
            $filtered_episodes[] = $episode;
          }
        
          // If we've met the number of items requested, we're done and can
          // return the results.
          if (count($filtered_episodes) >= $count) {
            // Add a flag for whether an asset is behind the Passport paywall.
            foreach ($filtered_episodes as &$filtered_episode) {
              $filtered_episode['attributes']['is_passport'] =
                $this->checkPassport($filtered_episode);
            }
            //return $filtered_episodes;
            return $this->mapEpisodeValues($filtered_episodes);
          }
        }
      
        $episodes = $filtered_episodes;
      }
    }
  
    // If we've met the number of items requested, we're done and can
    // return the results.
    if (count($episodes) >= $count) {
      // Limit the results to the number requested.
      $episodes = array_slice($episodes, 0, $count);
    
      // Add a flag for whether an asset is behind the Passport paywall.
      foreach ($episodes as &$episode) {
        $episode['attributes']['is_passport'] = $this->checkPassport($episode);
      }
    
      return $this->mapEpisodeValues($episodes);
    }
  
  
    // If there aren't enough results and $include_clips and $include_previews
    // are TRUE, we'll need to do a second call to fill out the results.
    if (($include_clips) || ($include_previews)) {
      // How many more items are needed?
      $needed = $count - count($episodes);
    
      if ($include_clips) {
        $queryargs['type'] = 'clip';
      }
      if ($include_previews) {
        $queryargs['type'] = 'preview';
      }
      if ($include_clips && $include_previews) {
        $queryargs['type'] = 'clip&type=preview';
      }
    
      $more_episodes = $client->get_assets($queryargs);
    
      // Filter out unwanted items, such as those without players.
      if ($require_players) {
      
        $filtered_moreeps = array();
      
        foreach ($more_episodes as $episode) {
          // If the asset is available, add to the results.
          if ($this->checkAvailability($episode)) {
            $filtered_moreeps[] = $episode;
          }
        
          // If we've met the number of items requested, we're done and can
          // return the results.
          if (count($filtered_moreeps) >= $needed) {
            $episodes = array_merge($episodes, $filtered_moreeps);
          
            // Add a flag for whether an asset is behind the Passport paywall.
            foreach ($episodes as &$thisepisode) {
              $thisepisode['attributes']['is_passport'] = $this->checkPassport($thisepisode);
            }
            return $this->mapEpisodeValues($episodes);
          }
        }
        $more_episodes = $filtered_moreeps;
      }
    
      $episodes = array_merge($episodes, $more_episodes);
    
      // Add a flag for whether an asset is behind the Passport paywall.
      foreach ($episodes as &$episode) {
        $episode['attributes']['is_passport'] = $this->checkPassport($episode);
      }
  
      return $this->mapEpisodeValues($episodes);
    }
  
    // Add a flag for whether an asset is behind the Passport paywall.
    foreach ($episodes as &$episode) {
      $episode['attributes']['is_passport'] = $this->checkPassport($episode);
    }
    
    // Finally, whatever we have is good enough.
    return $this->mapEpisodeValues($episodes);
    
  }
  
  /**
   * Check to see if an asset is available to be viewed.
   *
   * @param array $asset
   *   The asset array as returned from the API.
   *
   * @return bool
   *   Returns TRUE if available.
   */
  private function checkAvailability(array $asset) {
    if (!isset($asset['attributes']['availabilities'])) {
      return FALSE;
    }
    else {
      // Get the current date/time.
      $now = time();
      
      // Convert the station members availabilities to timestamps.
      $station_members_start = strtotime($asset['attributes']['availabilities']['station_members']['start']);
      $station_members_end = strtotime($asset['attributes']['availabilities']['station_members']['end']);
      
      // If now is within the station members availability window, then the
      // asset is available.
      $return = FALSE;
      if ($now >= $station_members_start && ($now <= $station_members_end ||
          empty($station_members_end))) {
        $return = TRUE;
      }
      return $return;
    }
  }
  
  /**
   * Check to see if an asset is behind the Passport paywall.
   *
   * @param array $asset
   *   The asset array as returned from the API.
   *
   * @return bool
   *   Returns TRUE if behind the Passport paywall.
   */
  private function checkPassport(array $asset) {
    if (!isset($asset['attributes']['availabilities'])) {
      return FALSE;
    }
    else {
      // Get the current date/time.
      $now = time();
      
      // Convert the member availabilities to timestamps.
      $public_start = strtotime($asset['attributes']['availabilities']['public']['start']);
      $public_end = strtotime($asset['attributes']['availabilities']['public']['end']);
      $all_members_start = strtotime($asset['attributes']['availabilities']['all_members']['start']);
      $all_members_end = strtotime($asset['attributes']['availabilities']['all_members']['end']);
      $station_members_start = strtotime($asset['attributes']['availabilities']['station_members']['start']);
      $station_members_end = strtotime($asset['attributes']['availabilities']['station_members']['end']);
      
      // Start broad and narrow it down.
      $return = FALSE;
      // Check to see if we're within the public window.
      if ($now >= $public_start && $now <= $public_end) {
        // This asset is available to all, so is not in Passport.
        $return = FALSE;
      }
      // Check to see if we're within the all members window.
      elseif ($now >= $all_members_start && $now <= $all_members_end) {
        // This asset is available to all members, so is in Passport.
        $return = TRUE;
      }
      // Check to see if we're within the station members window.
      elseif ($now >= $station_members_start && $now <= $station_members_end) {
        $return = TRUE;
      }
      return $return;
    }
  }
  
  
  /**
   *
   */
  public function mapEpisodeValues($episodes) {
    $results = array();
    foreach ($episodes as $i => $episode) {
      $results[$i]['slug'] = $episode['attributes']['slug'];
      $results[$i]['title'] = $episode['attributes']['title'];
      $results[$i]['player'] = $episode['attributes']['player_code'];
      $results[$i]['is_passport'] = $episode['attributes']['is_passport'];
      $results[$i]['description'] = $episode['attributes']['description_long'];
    }
    return $results;
  }
  
}
