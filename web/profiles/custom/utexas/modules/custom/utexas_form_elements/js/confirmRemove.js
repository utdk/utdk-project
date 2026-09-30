(function ($, Drupal) {
  "use strict";

  /* A JS-based confirm button for removing component items (#2315). */
  Drupal.behaviors.utexas_form_elements = {
    attach: function (context, settings) {
      // Remove the original click event to avoid a 'double click'.
      $('.confirm-remove').unbind('click');
      $('.confirm-remove').click(function () {
        let value = $(this).attr('value');
        if (confirm('Are you sure you want to ' + value.toLowerCase() + '?')) {
          let originalTarget = $(this).attr('data-remove-target');
          console.log(originalTarget);
          let removeButton = $('input[name="' + originalTarget + '"]');
          if (removeButton !== null) {
            removeButton.click();
          }
        }
        // Take no action.
        return false;
      });
    }
  }
})(jQuery, Drupal);
