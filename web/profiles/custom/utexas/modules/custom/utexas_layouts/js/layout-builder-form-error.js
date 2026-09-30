  (function ($, Drupal) {
    'use strict';
    Drupal.behaviors.layoutBuilderFormError = {
      attach: function () {
        $("div#error-message-bottom").remove();
        if ($("#layout-builder-modal form.layout-builder-configure-block div[role = 'alert']").length) {
          var errorMessage = $("#layout-builder-modal div[data-drupal-messages]").html();
          $("#layout-builder-modal").append('<div id="error-message-bottom">' + errorMessage + '</div>');
        }
      }
    };
  })(jQuery, Drupal);
