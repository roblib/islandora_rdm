<?php

namespace Drupal\islandora_rdm_migration\Plugin\migrate\process;

use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Static map with dot placeholder support.
 *
 * Copied and modified from example at
 * https://github.com/FreelyAgency/drupal8-migrate-process-static-map-dots
 *
 * This plugin changes the current value based on a static lookup map but
 * it also replace all dot placeholders to real dots (this is the only
 * difference to static_map plugin).
 *
 * @MigrateProcessPlugin(
 *   id = "static_map_dots"
 * )
 */
class StaticMapDots extends ProcessPluginBase {

  public static $dotPlaceholder = '&#46;';

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $new_value = $value;

    if (is_array($value)) {
      if (!$value) {
        throw new MigrateException('Can not lookup without a value.');
      }
    }
    else {
      $new_value = array($value);
    }

    // Replace all dot placeholders in map.
    $this->configuration['map'] = $this->preprocessDotsPlaceholders($this->configuration['map']);

    $new_value = NestedArray::getValue($this->configuration['map'], $new_value, $key_exists);
    if (!$key_exists) {
      if (array_key_exists('default_value', $this->configuration)) {
        if (!empty($this->configuration['bypass'])) {
          throw new MigrateException('Setting both default_value and bypass is invalid.');
        }
        return $this->configuration['default_value'];
      }
      if (empty($this->configuration['bypass'])) {
        throw new MigrateSkipRowException();
      }
      else {
        return $value;
      }
    }

    return $new_value;
  }

  /**
   * Replace placeholder to dot.
   *
   * Search for all dot placeholders in map (@see self::$dotPlaceholder)
   * and replace it to dot.
   */
  protected function preprocessDotsPlaceholders($map) {
    $preprocessedMap = array();

    foreach ($map as $key => $value) {
      $preprocessedKey = str_replace(self::$dotPlaceholder, '.', $key);
      $preprocessedValue = str_replace(self::$dotPlaceholder, '.', $value);

      $preprocessedMap[$preprocessedKey] = $preprocessedValue;
    }

    return $preprocessedMap;
  }

}
