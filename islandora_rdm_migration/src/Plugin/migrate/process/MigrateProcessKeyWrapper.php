<?php
namespace Drupal\islandora_rdm_migration\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Wrap an element into a keyed array as expected by the sub_proces plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "key_wrapper"
 * )
 */
class MigrateProcessKeyWrapper extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $new_value = array(
        'key_wrapper' => ['PID' => $value],
    );
    $value = $new_value;
    return $value;
  }

}
