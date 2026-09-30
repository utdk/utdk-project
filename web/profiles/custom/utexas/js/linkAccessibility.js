(($, Drupal, once) => {
  "use strict";

  /**
   * @file
   *
   * Progressive enhancements to make links more accessible.
   */
  /**
   * If a link has target="_blank"/"new", append an aria-label.
   * @param {object} el The link element.
   */
  function modifyLink(el) {
    const classes = el.getAttribute("class");
    const target = el.getAttribute("target");
    let label = el.getAttribute("aria-label") ?? el.innerText;
    let labelAppendage = "";
    if (classes) {
      if (classes.includes("ut-cta-link--external")) {
        labelAppendage += "; external link";
      }
      if (classes.includes("ut-cta-link--lock")) {
        labelAppendage += "; restricted link";
      }
    }
    if (target === "_blank" || target === "new") {
      labelAppendage += "; opens in new window";
    }
    if (labelAppendage) {
      const ariaHidden = el.getAttribute("aria-hidden");
      if (ariaHidden !== "true") {
        label += labelAppendage;
        el.setAttribute("aria-label", label);
      }
    }
  }

  Drupal.behaviors.utexasLinkAccessibility = {
    attach(context) {
      window.addEventListener(
        "load",
        () => {
          $(once("link", "a", context)).each(function handleLink() {
            modifyLink(this);
          });
        },
        { once: true }
      );
    }
  };
})(jQuery, Drupal, once);
