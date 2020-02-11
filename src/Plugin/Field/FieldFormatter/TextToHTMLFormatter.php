<?php

namespace Drupal\islandora_rdm\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'html' formatter.
 *
 * @FieldFormatter(
 *   id = "texttohtml",
 *   label = @Translation("Text as HTML"),
 *   field_types = {
 *     "text_long"
 *   }
 * )
 */
class TextToHTMLFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $item) {
      $value = $item->value;
      if (empty($value)) {
        $value = $item->getValue();
      }
      $value = "<div><details><summary>Extracted Text</summary> $value</details></div>";
      $elements[] = array(
        '#markup' => $value,
      );
    }
    return $elements;
  }
}
