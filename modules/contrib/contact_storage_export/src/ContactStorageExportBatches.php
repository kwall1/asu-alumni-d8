<?php

/**
 * @file
 * Class to handle batch process of CSV export.
 */

use Drupal\csv_serialization\Encoder\CsvEncoder;
use Symfony\Component\HttpFoundation\Response;

class ContactStorageExportBatches {
  
  /**
   * Process callback for the batch set the export form.
   */
  public static function processBatch($settings, &$context) {
    if (empty($context['sandbox'])) {
      
      // Store data in results for batch finish.
      $context['results']['data'] = [];
      $context['results']['settings'] = $settings;
      
      // Set initial batch progress.
      $context['sandbox']['settings'] = $settings;
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max'] = self::getMax($settings);
      
    }
    else {
      $settings = $context['sandbox']['settings'];
    }
    
    if ($context['sandbox']['max'] == 0) {
      
      // If we have no rows to export, immediately finish.
      $context['finished'] = 1;
      
    }
    else {
    
      // Get the next batch worth of data.
      self::getContactFormData($settings, $context);
      
      // Check if we are now finished.
      if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
        $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
      }
      
    }
    
  }
  
  /**
   * Get the submissions for the given contact form.
   *
   * @param string
   *   The contact form.
   */
  private static function getContactFormData($settings, &$context) {
    
    $limit = 25;
    $query = \Drupal::entityQuery('contact_message');
    $query->condition('contact_form', $settings['contact_form']);
    $query->range($context['sandbox']['progress'], $limit);
    if ($mids = $query->execute()) {
      if ($messages = entity_load_multiple('contact_message', $mids)) {
        self::prepareMessages($messages, $context);
      }
    }
  }
  
  /**
   * Get max amount of messages to export.
   * 
   * @return int
   *   The maximum number of messages to export.
   */
  private static function getMax($settings) {
    $query = \Drupal::entityQuery('contact_message');
    $query->condition('contact_form', $settings['contact_form']);
    $query->count();
    $result = $query->execute();
    return ($result ? $result : 0);
  }
  
  /**
   * Prepare the contact_message objects for export to CSV.
   * 
   * @param array $messages
   *   The contact_message objects.
   */
  private static function prepareMessages($messages, &$context) {
    
    $labels = self::getLabels($messages);
    foreach ($messages as $contact_message) {
      
      $row = [];
      $id = $contact_message->id();
      
      // Get the message values we want to export.
      $values = $contact_message->toArray();
      $values = self::removeColumns($values);
      
      if (isset($values['created']) && !empty($values['created'])) {
        $values['created'][0]['value'] = format_date($values['created'][0]['value']);
      }
      
      // Set the keys to be readable labels.
      foreach ($values as $key => $value) {
        $row[$labels[$key]] = $value;
      }
      
      // Add the row to our CSV data.
      $context['results']['data'][] = $row;
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $id;
      
      // Set the current message
      $message = 'Processed up to Contact Message ID @id. ';
      $message .= 'Your file will download immediately when complete.';
      $context['message'] = t($message, ['id' => $id]);
      
    }
  }
  
  /**
   * Remove undesired columns from export.
   * 
   * @param array $values
   *   The keyed values from the contact_message.
   * @return array
   *   The updated keyed values after removals.
   */
  private static function removeColumns($values) {
    unset(
      $values['uuid']
    );
    return $values;
  }
  
  /**
   * Get the labels from the field definitions.
   * 
   * @param array $messages
   *   The contact_message objects.
   * @return array
   *   The labels.
   */
  private static function getLabels($messages) {
    $labels = [];
    if ($fields = reset($messages)->getFieldDefinitions()) {
      foreach ($fields as $key => $field) {
        if ($label = $field->getLabel()) {
          
          // Remove characters not allowed in keys of associative arrays.
          $label = filter_var(
            $label, 
            FILTER_SANITIZE_STRING, 
            FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_NO_ENCODE_QUOTES
          );          
          $labels[$key] = $label;
          
        }
      }
    }
    return $labels;
  }
  
  /**
   * Finish callback for the batch set the export form.
   */
  public static function finishBatch($success, $results, $operations) {
    if ($success) {
      if ($results['data']) {
        
        // Encode the CSV data into a string.
        $encoder = new CsvEncoder();
        $csv_string = $encoder->encode($results['data'], 'csv');
        
        // Prepare the CSV headers.
        $filename = addslashes($results['settings']['filename']);
        $headers = array(
          'Content-Type' => 'text/csv',
          'Content-Transfer-Encoding' => 'binary',
          'Pragma' => 'no-cache',
          'Expires' => '0',
          'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        );
        
        // Send the response and exit.
        $response = new Response($csv_string, 200, $headers);
        $response->send();
        exit;
        
      } 
      else {
        $message = t('There was no data to export.');
      }
    }
    else {
      $message = t('Finished with an error.');
    }
  }
  
}
