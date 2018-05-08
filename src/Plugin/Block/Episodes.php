<?php

namespace Drupal\pbs_media_manager\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pbs_media_manager\Client\APIConnect;

/**
 * Provides a 'Episodes' block.
 *
 * @Block(
 *  id = "episodes",
 *  admin_label = @Translation("Episodes"),
 * )
 */
class Episodes extends BlockBase {
  
  /**
   * {@inheritdoc}
   *
   * This method sets the block default configuration. This configuration
   * determines the block's behavior when a block is initially placed in a
   * region. Default values for the block configuration form should be added to
   * the configuration array. System default configurations are assembled in
   * BlockBase::__construct() e.g. cache setting and block title visibility.
   *
   * @see \Drupal\block\BlockBase::__construct()
   */
  public function defaultConfiguration() {
    return [
      'pbs_mm_episodes_count' => 5,
      'pbs_mm_sort' => '-premiered_on',
      'pbs_mm_include_specials' => TRUE,
      'pbs_mm_require_players' => TRUE,
      'pbs_mm_include_clips' => FALSE,
      'pbs_mm_include_previews' => FALSE,
    ];
  }
  
  /**
   * {@inheritdoc}
   *
   * This method defines form elements for custom block configuration. Standard
   * block configuration fields are added by BlockBase::buildConfigurationForm()
   * (block title and title visibility) and BlockFormController::form() (block
   * visibility settings).
   *
   * @see \Drupal\block\BlockBase::buildConfigurationForm()
   * @see \Drupal\block\BlockFormController::form()
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    
    $config = $this->getConfiguration();
    
    $form['pbs_mm_episodes_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of episodes to display'),
      '#description' => $this->t('The maximum number of episodes that will display in the block. If not enough episodes are returned, the block will display what is available.'),
      '#default_value' => isset($config['pbs_mm_episodes_count']) ? $config['pbs_mm_episodes_count'] : '',
    ];
  
    $form['pbs_mm_sort'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort by:'),
      '#description' => $this->t('Show the latest n episodes in descending order, or the first n episodes in ascending order.'),
      '#options' => [
        '-premiered_on' => $this->t('Premiere date, descending'),
        'premiered_on' => $this->t('Premiere date, ascending'),
      ],
      '#default_value' => isset($config['pbs_mm_sort']) ? $config['pbs_mm_sort'] : '',
    ];
    
    $form['pbs_mm_include_specials'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include specials'),
      '#description' => $this->t('Include specials in the list of returned episodes.'),
      '#default_value' => isset($config['pbs_mm_include_specials']) ?
        $config['pbs_mm_include_specials'] : '',
    ];
  
    $form['pbs_mm_require_players'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require players'),
      '#description' => $this->t('Only include results that are available to be viewed.'),
      '#default_value' => isset($config['pbs_mm_require_players']) ?
        $config['pbs_mm_require_players'] : '',
    ];
  
    $form['pbs_mm_include_clips'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include clips'),
      '#description' => $this->t('Include clips if there are not enough episodes retrieved.'),
      '#default_value' => isset($config['pbs_mm_include_clips']) ?
        $config['pbs_mm_include_clips'] : '',
    ];

    $form['pbs_mm_include_previews'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include previews'),
      '#description' => $this->t('Include previews if there are not enough episodes retrieved.'),
      '#default_value' => isset($config['pbs_mm_include_clips']) ?
        $config['pbs_mm_include_clips'] : '',
    ];
    return $form;
  }
  
  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['pbs_mm_episodes_count'] = $values['pbs_mm_episodes_count'];
    $this->configuration['pbs_mm_sort'] = $values['pbs_mm_sort'];
    $this->configuration['pbs_mm_include_specials'] = $values['pbs_mm_include_specials'];
    $this->configuration['pbs_mm_require_players'] = $values['pbs_mm_require_players'];
    $this->configuration['pbs_mm_include_clips'] = $values['pbs_mm_include_clips'];
    $this->configuration['pbs_mm_include_previews'] = $values['pbs_mm_include_previews'];
    
  }
  
  
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    
    //TODO: Get this from config also.
    //Get the slug from the URL
    $slug = \Drupal::routeMatch()->getParameter('pbs_mm_show_id');

    if ($slug) {
      
      // Get additional parameters from the block config.
      $count = $this->configuration['pbs_mm_episodes_count'];
      $sort = $this->configuration['pbs_mm_sort'];
      $include_specials = $this->configuration['pbs_mm_include_specials'];
      $require_players = $this->configuration['pbs_mm_require_players'];
      $include_clips = $this->configuration['pbs_mm_include_clips'];
      $include_previews = $this->configuration['pbs_mm_include_previews'];
      $episodes = $this->getEpisodes($slug, $count, $sort,
      $include_specials,
        $require_players, $include_clips, $include_previews);
  
      $build = [];
      $build['episodes']['#theme'] = 'pbs_mm_asset_teasers';
      $build['episodes']['#asset_teasers'] = $episodes;
      $build['episodes']['#cache'] = [
        'contexts' => ['url.path'],
      ];
  
      return $build;
      
    }
    
    return NULL;
  }
  
  
  /**
   * Uses the Media Manager API to build list of episodes for the Show.
   *
   * @param string $id
   *   The id or slug of the show.
   * @param int $count
   *   The number of episodes to retrieve.
   * @param string $sort
   *   How to sort the results.
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
  public function getEpisodes($id, $count, $sort = '-premiered_on',
    $include_specials =
  TRUE,
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
      'sort' => $sort,
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
      
      $direction = 'sortDesc';
      if ($sort == 'premiered_on') {
        $direction = 'sortAsc';
      }

      usort($episodes, array($this, $direction));

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
   * Comparative callback function to sort descending.
   *
   * @param mixed $a
   *   The first item.
   * @param mixed $b
   *   The second item.
   *
   * @return integer
   */
  static function sortDesc($a, $b) {
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
  }
  
  /**
   * Comparative callback function to sort descending.
   *
   * @param mixed $a
   *   The first item.
   * @param mixed $b
   *   The second item.
   *
   * @return integer
   */
  static function sortAsc($a, $b) {
    if (isset($b['attributes']) && isset($a['attributes'])) {
      // If the episodes premiered on the same day...
      if ($b['attributes']['premiered_on'] ==
        $a['attributes']['premiered_on']) {
        // And if there's an episode ordinal for both episodes...
        if (isset($a['attributes']['episode']['attributes']['ordinal']) &&
          isset($b['attributes']['episode']['attributes']['ordinal'])) {
          // Sort by the ordinal.
          return $a['attributes']['episode']['attributes']['ordinal'] -
            $b['attributes']['episode']['attributes']['ordinal'];
          
        }
      }
      // Otherwise, just sort by premiered_on.
      return strcmp($a['attributes']['premiered_on'],
        $b['attributes']['premiered_on']);
      
    }
    else {
      return FALSE;
    }
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
      foreach ($episode['attributes'] as $key => $value) {
        $results[$i][$key] = $value;
      }
    }
    return $results;
  }
  
  
}
