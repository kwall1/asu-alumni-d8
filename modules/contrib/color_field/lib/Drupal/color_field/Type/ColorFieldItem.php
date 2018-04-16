<?php

/**
 * @file
 * Contains \Drupal\color_field\Type\ColorFieldItem.
 */

namespace Drupal\color_field\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'color_field' entity field item.
 */
class ColorFieldItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see ColorFieldItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['rgb'] = array(
        'type' => 'string',
        'label' => t('Color value'),
      );
      static::$propertyDefinitions['alfa'] = array(
        'type' => 'float',
        'label' => t('Alfa value'),
      );
    }
    return static::$propertyDefinitions;

  }
}
