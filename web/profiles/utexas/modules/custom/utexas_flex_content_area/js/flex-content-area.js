(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.flexContentAreaVideo = {
    attach: function () {
      $('.ut-flex-content-area').each(function () {
        var containerWidth = $(this).find('.image-wrapper').width();
        if ($(this).find('.image-wrapper > iframe').length) {
          var ratio = $(this).find('.image-wrapper').attr('data-ratio');
          if (ratio !== "") {
            var newHeight = ratio * containerWidth;
            var wrapper = $(this).find('.image-wrapper > iframe');
            wrapper.height(newHeight + "px");
          }
        }
      });
    }
  };
})(jQuery, Drupal);