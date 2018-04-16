<?php

namespace Drupal\kwall_map\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class KWALLMapForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kwall_map_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('kwall_map.settings');

    $form['settings']['overlay_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Overlay path'),
      '#description' => $this->t('Set relative or absolute path to custom overlay. Tokens supported. Empty for default.'),
      '#default_value' => $settings['google_map_settings']['overlay_path'],
    ];

    $form['settings']['sw_lat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Southwest Latitude'),
      '#description' => $this->t('Set the Southwest Latitude coordinates. Empty for default.'),
      '#default_value' => $settings['google_map_settings']['sw_lat'],
    ];
    $form['settings']['sw_lon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Southwest Longitude'),
      '#description' => $this->t('Set the Southwest Longitude coordinates. Empty for default.'),
      '#default_value' => $settings['google_map_settings']['sw_lon'],
    ];
    $form['settings']['ne_lat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Northeast Latitude'),
      '#description' => $this->t('Set the Northeast Latitude coordinates. Empty for default.'),
      '#default_value' => $settings['google_map_settings']['ne_lat'],
    ];
    $form['settings']['ne_lon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Northeast Longitude'),
      '#description' => $this->t('Set the Northeast Longitude coordinates. Empty for default.'),
      '#default_value' => $settings['google_map_settings']['ne_lon'],
    ];

    $form['settings']['style'] = [
      '#type' => 'textarea',
      '#title' => $this->t('JSON styles'),
      '#description' => $this->t('A JSON encoded styles array to customize the presentation of the Google Map.'),
      '#default_value' => $settings['google_map_settings']['style'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('kwall_map.settings');
    $config->set('kwall_map.source_text', $form_state->getValue('source_text'));
    $config->set('kwall_map.page_title', $form_state->getValue('google_map_settings'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'kwall_map.settings',
    ];
  }

}