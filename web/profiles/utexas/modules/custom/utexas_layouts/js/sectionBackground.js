  (function ($, Drupal) {
    'use strict';
    Drupal.behaviors.sectionBackground = {
      attach: function () {
        $('input[name="layout_settings[background-accent-wrapper][background-accent][media_library_selection]"]').on("change", function () {
          if (this.value === '') {
            $('input[name="layout_settings[background-accent-wrapper][blur]"]').attr("disabled", true);
          }
          else {
            $('input[name="layout_settings[background-accent-wrapper][blur]"]').attr("disabled", false);
          }
        }).trigger("change");
      }
    };
  })(jQuery, Drupal);
