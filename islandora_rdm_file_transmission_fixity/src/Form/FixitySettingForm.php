<?php

namespace Drupal\islandora_rdm_file_transmission_fixity\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class FixitySettingForm extends ConfigFormBase {
  /**
   * The path to stored config file.
   *
   * @var string
   */
  protected $configFilepath;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_rdm_file_transmission_fixity_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'islandora_rdm_file_transmission_fixity.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('islandora_rdm_file_transmission_fixity.settings');

    $form['expected_fixity_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of expected checksum field'),
      '#description' => $this->t('Each Media Type must have a field allowing the user to enter the SHA1 checksum of the file to be uploaded.'),
      '#default_value' => $config->get('expected_fixity_field') ? $config->get('expected_fixity_field') : 'field_expected_checksum',
    ];
    $form['actual_fixity_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of actual checksum field'),
      '#description' => $this->t('Each Media Type must have a field to hold the actual checksum.'),
      '#default_value' => $config->get('actual_fixity_field') ? $config->get('actual_fixity_field') : 'field_actual_checksum',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('islandora_rdm_file_transmission_fixity.settings')
      ->set('expected_fixity_field', trim($form_state->getValue('expected_fixity_field')))
      ->set('actual_fixity_field', trim($form_state->getValue('actual_fixity_field')))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
