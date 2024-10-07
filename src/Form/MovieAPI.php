<?php

namespace Drupal\filmbuff\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the form for the Filmbuff API configuration.
 */
class MovieAPI extends ConfigFormBase {

  const CONFIG_NAME = 'filmbuff.movie_api_config';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'filmbuff_api_config_page';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base URL'),
      '#description' => $this->t('This is the API Base URL.'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_base_url') ?: 'https://api.themoviedb.org',
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key (v3 auth)'),
      '#description' => $this->t('This is the API key that will be used to access the API.'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_key') ?: '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submitted_values = $form_state->getValues();

    $this->configFactory()->getEditable(self::CONFIG_NAME)
      ->set('api_base_url', $submitted_values['api_base_url'])
      ->set('api_key', $submitted_values['api_key'])
      ->save();

    $this->messenger()->addMessage($this->t('Your new configuration has been saved.'));
  }

}
