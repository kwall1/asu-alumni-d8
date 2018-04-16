<?php

/**
 * @file
 * Definition of Drupal\color_field\Plugin\field\formatter\MailToFormatter.
 */

namespace Drupal\color_field\Plugin\field\formatter;

use Drupal\field\Annotation\FieldFormatter;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\Formatter\FormatterBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin implementation of the 'color_field_hex' formatter.
 *
 * @FieldFormatter(
 *   id = "color_field_hex",
 *   module = "color_field",
 *   label = @Translation("Color Hex"),
 *   field_types = {
 *     "color_field"
 *   }
 * )
 */
class ColorFieldHexFormatter extends FormatterBase {

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::viewElements().
   */
  public function viewElements(EntityInterface $entity, $langcode, array $items) {
    $elements = array();

    foreach ($items as $delta => $item) {
      // The text value has no text format assigned to it, so the user input
      // should equal the output, including newlines.
      $elements[$delta] = array('#markup' => $item['rgb']);
    }

    return $elements;
  }

}
