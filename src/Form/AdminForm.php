<?php

namespace Drupal\islandora_rdm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdminForm.
 */
class AdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_rdm_admin_form';
  }

  /**
   * Get the config names.
   */
  protected function getEditableConfigNames() {
    return [
      'islandora_rdm.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('islandora_rdm.settings');
    $default_endpoint = $config->get('sparql_endpoint') ? $config->get('sparql_endpoint') : 'http://localhost:8080/bigdata/namespace/islandora/sparql';
    $form['sparql_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SPARQL endpoint'),
      '#description' => $this->t('URL to which queries will be sent. Address will normally take the form of [PROTOCOL]://[server]:[PORT]/bigdata/namespace/[your namespace (usually Islandora)]/sparql'),
      '#maxlength' => 120,
      '#size' => 120,
      '#default_value' => $default_endpoint,
      '#weight' => '0',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $values = $form_state->getValues();
    $this->config('islandora_rdm.settings')
      ->set('sparql_endpoint', $values['sparql_endpoint'])
      ->save();

  }

}
