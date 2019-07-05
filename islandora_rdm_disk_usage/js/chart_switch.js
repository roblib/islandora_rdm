/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

alert( 'Hello, world!' );

function disk_usage_report_element_info_alter(array $types) {
  if (isset($types['table'])) {
    $types['table']['#attached']['library'][] = 'your_module/library_name';
  }
}