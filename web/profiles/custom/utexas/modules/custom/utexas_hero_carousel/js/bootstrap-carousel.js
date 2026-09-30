/**
 * @file
 * Bootstrap Carousel customizations.
 */

/* eslint-disable */
(function(Drupal, drupalSettings) {
  "use strict";

  /**
   * Adds pause button to Bootstrap carousels.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.utexasHeroCarousel = {
    attach() {
      const carouselOptions = drupalSettings.utexas_hero_carousel;

      document.querySelectorAll(".utexas-hero-carousel").forEach(carousel => {
        const { id } = carousel;
        const options = carouselOptions[id];
        let autoplay = false;
        if (options.autoplay === "1") {
          autoplay = "carousel";
        }
        /* eslint-disable */
        new bootstrap.Carousel(carousel, {
          pause: "hover",
          ride: autoplay,
          interval: options.interval
        });
        if (autoplay === false) {
          const playPause = carousel.querySelector("[data-autoplay]");
          playPause.dataset.autoplay = "pause";
          playPause.innerHTML = '<span class="visually-hidden">Play</span>';
        }
      });

      function toggleCarousel(event) {
        const carouselId = event.currentTarget.dataset.bsTarget;
        const state = event.currentTarget.dataset.autoplay;
        if (state === "cycle") {
          event.currentTarget.dataset.autoplay = "pause";
          event.currentTarget.innerHTML =
            '<span class="visually-hidden">Play</span>';
        } else {
          event.currentTarget.dataset.autoplay = "cycle";
          event.currentTarget.innerHTML =
            '<span class="visually-hidden">Pause</span>';
        }
        if (carouselId !== "null") {
          const carousel = document.getElementById(carouselId);
          const options = carouselOptions[carouselId];
          let autoplay = "carousel";
          let intervalValue = options.interval ?? false;
          if (state === "cycle") {
            autoplay = false;
            intervalValue = false;
          }
          /* eslint-disable no-undef */
          const oldCarousel = bootstrap.Carousel.getOrCreateInstance(carousel);
          oldCarousel.dispose();
          /* eslint-disable no-new */
          new bootstrap.Carousel(carousel, {
            pause: "hover",
            ride: autoplay,
            interval: intervalValue
          });
        }
      }

      window.addEventListener("load", function _() {
        document.querySelectorAll("[data-autoplay]").forEach(pauseButton => {
          pauseButton.addEventListener("click", toggleCarousel);
        });
      });
    }
  };
})(Drupal, drupalSettings);
