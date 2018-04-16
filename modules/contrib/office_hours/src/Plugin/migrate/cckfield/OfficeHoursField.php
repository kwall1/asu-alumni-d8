<?php

namespace Drupal\office_hours\Plugin\migrate\cckfield;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\cckfield\CckFieldPluginBase;

/**
 * @MigrateCckField(
 *   id = "office_hours_field",
 *   core = {7},
 *   type_map = {
 *    "office_hours" = "office_hours"
 *   }
 * )
 */
class OfficeHoursField extends CckFieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    return [
      'office_hours' => 'office_hours_default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    return [
      'office_hours' => 'office_hours_default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processCckFieldValues(MigrationInterface $migration, $field_name, $data) {
    $process = [
      'plugin' => 'office_hours_field_plugin',
      'source' => 'field_office_hours',
    ];
    $migration->mergeProcessOfProperty($field_name, $process);
  }

}
