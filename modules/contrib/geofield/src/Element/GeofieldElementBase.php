<?php

namespace Drupal\geofield\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a base class for Geofield Form elements.
 */
abstract class GeofieldElementBase extends FormElement {

  /**
   * Array declaring components.
   *
   * @var array
   */
  public static $components = [];

  /**
   * Generates a Geofield generic component based form element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element. Note that $element must be taken by reference here, so processed
   *   child elements are taken over into $form_state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function elementProcess(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element['#tree'] = TRUE;
    $element['#input'] = TRUE;

    foreach (static::$components as $name => $component) {
      $element[$name] = [
        '#type' => 'textfield',
        '#title' => $component['title'],
        '#required' => (!empty($element['#required'])) ? $element['#required'] : FALSE,
        '#default_value' => (isset($element['#default_value'][$name])) ? $element['#default_value'][$name] : '',
        '#attributes' => [
          'class' => ['geofield-' . $name],
        ],
      ];
    }

    unset($element['#value']);
    // Set this to false always to prevent notices.
    $element['#required'] = FALSE;

    return $element;
  }

  /**
   * Validates a Geofield generic component based form element.
   *
   * @param array $element
   *   The element being processed.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public static function elementValidate(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $all_filled = TRUE;
    $any_filled = FALSE;
    $error_label = isset($element['#error_label']) ? $element['#error_label'] : $element['#title'];
    foreach (static::$components as $key => $component) {
      if (!empty($element[$key]['#value'])) {
        if (!is_numeric($element[$key]['#value'])) {
          $form_state->setError($element[$key], t('@title: @component_title is not numeric.', ['@title' => $error_label, '@component_title' => $component['title']]));
        }
        elseif (abs($element[$key]['#value']) > $component['range']) {
          $form_state->setError($element[$key], t('@title: @component_title is out of bounds.', ['@title' => $error_label, '@component_title' => $component['title']]));
        }
      }
      if ($element[$key]['#value'] == '') {
        $all_filled = FALSE;
      }
      else {
        $any_filled = TRUE;
      }
    }
    if ($any_filled && !$all_filled) {
      foreach (self::$components as $key => $component) {
        if ($element[$key]['#value'] == '') {
          $form_state->setError($element[$key], t('@title: @component_title must be filled too.', ['@title' => $error_label, '@component_title' => $component['title']]));
        }
      }
    }
  }

}
