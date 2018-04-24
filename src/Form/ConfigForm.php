<?php
namespace Drupal\pbs_media_manager\Form;

use Drupal\Core\Form\FormStateInterface;

class ConfigForm extends ConfigFormBase {
  public function getFormId() {
    return 'pbs_media_manager_settings_form';
  }
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfig();
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#default_value' => $config->get('key'),
      '#description' => $this->t('Your PBS Media Manager Key.'),
    ];

    $form['secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret'),
      '#default_value' => $config->get('secret'),
      '#description' => $this->t('Your PBS Media Manager secret.')
    ];
  
    $form['base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Station Portal URL'),
      '#default_value' => $config->get('base_uri'),
      '#description' => $this->t('The base URL for your station portal (e.g., https://watch.weta.org).'),
    ];
    return parent::buildForm($form, $form_state);
  }
}