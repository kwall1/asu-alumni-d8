<?php

/**
 * @file
 * Contains \Drupal\kwall_site_alert\Form\KwallSiteAlertAdmin.
 */

namespace Drupal\kwall_site_alert\Form;

use Drupal\Component\Utility\Random;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Cache\Cache;

class KwallSiteAlertAdmin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kwall_site_alert_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('kwall_site_alert.settings');
		
		$total = 5;
		for ($i = 1; $i <= $total; $i++) {
	    foreach (Element::children($form['group' . $i]) as $variable) {
	      $config->set($variable, $form_state->getValue($form['group' . $i][$variable]['#parents']));
	    }
	    
	    // Save a random key so that we can use it to track a 'dismiss' action for
			// this particular alert.
	    $random = new Random();
	    $config->set('kwall_site_alert_key' . $i, $random->string(16, TRUE));
	  }
		
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    // Flushes the pages after save.
    \Drupal::cache('render')->deleteAll();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['kwall_site_alert.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [];

    $form['description'] = [
      '#markup' => t('<h3>Use this form to add alert banners to the top of the site.</h3>
        <p>Make sure you select the checkbox if you want to turn the alerts on</p>')
    ];
    $form['#attributes'] = array('class' => array('list-group'));
      
    $total = 5; 
	
		for ($i = 1; $i <= $total; $i++) {
			
			$active = \Drupal::config('kwall_site_alert.settings')->get('kwall_site_alert_active' . $i);
			if ($active == 1) {
				$open = TRUE;
			} else {
				$open = FALSE;
			}

    	$form['group' . $i] = array(
			  '#type' => 'details',
			  '#title' => t('Alert #' . $i),
			  '#open' => $open,
			  '#attributes' => array('class' => array('list-group-item'))
			);
	    
	    $form['group' . $i]['kwall_site_alert_active' . $i] = [
	      '#type' => 'checkbox',
	      '#title' => t('If checked, this alert is active.'),
	      '#default_value' => $active,
	    ];
	
	    $form['group' . $i]['kwall_site_alert_severity' . $i] = [
	      '#type' => 'select',
	      '#title' => t('Severity'),
	      '#options' => [
	        'alert-info' => t('Info'),
	        'alert-success' => t('Success'),
	        'alert-warning' => t('Warning'),
	        'alert-danger' => t('Danger'),
	      ],
	      '#empty_option' => t('- SELECT -'),
	      '#default_value' => \Drupal::config('kwall_site_alert.settings')->get('kwall_site_alert_severity' . $i),
	      //'#required' => TRUE,
	    ];
	
	    $form['group' . $i]['kwall_site_alert_dismiss' . $i] = [
	      '#type' => 'checkbox',
	      '#title' => t('Make this alert dismissable?'),
	      '#default_value' => \Drupal::config('kwall_site_alert.settings')->get('kwall_site_alert_dismiss' . $i),
	    ];
	
	    // Need to load the text_format default a little differently.
	    $message = \Drupal::config('kwall_site_alert.settings')->get('kwall_site_alert_message' . $i);
	
	    $form['group' . $i]['kwall_site_alert_message' . $i] = [
	      '#type' => 'text_format',
	      '#title' => t('Alert Message'),
	      '#default_value' => isset($message['value']) ? $message['value'] : NULL,
	      //'#required' => TRUE,
	    ];
    
		}

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Alert Message'),
      '#button_type' => 'primary',
    ];

    // By default, render the form using theme_system_config_form().
    $form['#theme'] = 'system_config_form';

    // Cancelled out calling parent so we can have our own form submit.
    return $form;
  }

}
