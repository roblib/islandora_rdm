(function ($, Drupal) {
    Drupal.behaviors.islandora_rdm_disk_usage = {
        attach: function switch_button() {
            // Attach a click listener to the clear button.
            var btn = document.getElementById('chart-toggle-button');
            btn.addEventListener('click', function()
            {
                alert('Hello, world!');
            },);
        }
    };
}(jQuery, Drupal));

