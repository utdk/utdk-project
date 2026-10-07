(function utexasInstagramSlider($, Drupal, window) {
  Drupal.behaviors.utexasInstagram = {
    attach() {
      $(once("instagramFeed", ".utexas-instagram-feed")).each(
        function addSliderBehavior() {
          const $item = $(this).find(".utexas-instagram-feed__list-item");
          const visible = 1;
          let index = 0;
          const endIndex = $item.length / visible - 3;
          const mobileIndex = $item.length / visible - 1;

          const $backButton = $(this).find(
            ".utexas-instagram-feed__controls-prev"
          );
          const $forwardButton = $(this).find(
            ".utexas-instagram-feed__controls-next"
          );
          const enabledClass = "js-enabled";

          $forwardButton.on("keydown click", function navForward(e) {
            // If key was pressed, ignore all but spacebar and enter/return.
            if (e.keyCode && e.keyCode !== 32 && e.keyCode !== 13) {
              return;
            }
            // Prevent default 'keydown' and 'click' behavior.
            e.preventDefault();

            if (index < endIndex) {
              index += 1;
              $item.animate({ left: "-=35%" });
            }
            if (index < endIndex) {
              $(this).addClass(enabledClass);
            } else {
              $(this).removeClass(enabledClass);
            }
            if (index > 0) {
              $backButton.addClass(enabledClass);
            } else {
              $backButton.removeClass(enabledClass);
            }
          });

          $backButton.on("keydown click", function navBack(e) {
            // If key was pressed, ignore all but spacebar and enter/return.
            if (e.keyCode && e.keyCode !== 32 && e.keyCode !== 13) {
              return;
            }
            // Prevent default 'keydown' and 'click' behavior.
            e.preventDefault();

            if (index > 0) {
              index -= 1;
              $item.animate({ left: "+=35%" });
            }
            if (index > 0) {
              $(this).addClass(enabledClass);
            } else {
              $(this).removeClass(enabledClass);
            }
            if (index < endIndex) {
              $forwardButton.addClass(enabledClass);
            } else {
              $forwardButton.removeClass(enabledClass);
            }
          });

          $item.on("swiped-left", function goAhead() {
            if ($(window).width() < 651 && index < mobileIndex) {
              index += 1;
              $item.animate({ left: "-=100%" });
            }
          });

          $item.on("swiped-right", function rollBack() {
            if ($(window).width() < 651 && index > 0) {
              index -= 1;
              $item.animate({ left: "+=100%" });
            }
          });
        }
      );
    }
  };
})(jQuery, Drupal, this, this.document);
