<?php

/**
 * @file
 * Contains \Drupal\nama_migrate\Plugin\migrate\process\NamaDate.
 */

namespace Drupal\nama_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;


/**
 * This plugin converts Drupal 6 Date fields to Drupal 8.
 *
 * @MigrateProcessPlugin(
 *   id = "nama_date"
 * )
 */
class NamaDate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return '1999-12-11T17:00:00';
  }
}