<?php

namespace Drupal\contact_storage_export\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Settings form for config devel.
 */
class ContactStorageExportForm extends FormBase {
  
  /**
   * @var Request $request
   */
  protected $request;
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contact_storage_export';
  }
  

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    
    $contact_form = $request->get('contact_form');
    
    if ($contact_form) {
      
      // Store the requested contact form.
      $form['contact_form'] = [
        '#type' => 'hidden',
        '#value' => $contact_form,
      ];
      
    }
    elseif ($bundles = entity_get_bundles('contact_message')) {
      
      $options = [];
      foreach ($bundles as $key => $bundle) {
        $options[$key] = $bundle['label'];
      }
      
      $form['contact_form'] = [
        '#type' => 'select',
        '#title' => t('Contact form'),
        '#options' => $options,
        '#required' => TRUE,
      ];
      
    }
    
    // Allow the editor to override the default file name.
    if ($contact_form) {
      $filename = str_replace('_', '-', $contact_form);
    }
    else {
      $filename = 'contact-form';
    }
    $filename .= '-' . date('Y-m-d--h-i-s');
    $filename .= '.csv';
    $form['filename'] = [
      '#type' => 'textfield',
      '#title' => t('File name'),
      '#required' => TRUE,
      '#default_value' => $filename,
    ];
    
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export'),
      '#button_type' => 'primary',
      '#suffix' => t('Opens in a new window.'),
    ];
        
    // Open form in new window as our batch finish downloads the file.
    if (!isset($form['#attributes'])) {
      $form['#attributes'] = [];
    }
    $form['#attributes']['target'] = '_blank';
    
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
    
    // Path to the batch processing.
    $path = drupal_get_path('module', 'contact_storage_export');
    $path .= '/src/ContactStorageExportBatches.php';
    
    // Information to pass to the batch processing.
    $settings = $form_state->getValues();
    
    $batch = [
      'title' => t('Exporting'),
      'operations' => [
        [['ContactStorageExportBatches', 'processBatch'], [$settings]],
      ],
      'finished' => ['ContactStorageExportBatches', 'finishBatch'],
      'file' => $path,
    ];
    batch_set($batch);
    
  }

}
