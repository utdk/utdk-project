/**
 * @file
 * Make facet reset checkboxes display as links.
 */

(Drupal => {
  /**
   * Replace "Show all" checkbox with a link.
   *
   * @param {HTMLElement} item
   *   A Facets-provided checkbox for "Show all".
   */
  function fixCheckbox(item) {
    const label = item.querySelector("label");
    const link = item.querySelector("a");
    if (link !== null) {
      link.style.display = "block";
    }
    const input = item.querySelector("input");
    if (input !== null) {
      input.style.display = "none";
    }
    const facetItemCount = link.querySelector(".facet-item__count");
    if (facetItemCount) {
      facetItemCount.style.display = "none";
    }
    if (label !== null) {
      label.style.display = "none";
    }
  }

  /**
   * Turns a facet checkbox into a link.
   */
  function speedwayFacetsPrepCheckboxes() {
    const widgetLinks = document.querySelectorAll(".facet-item.facets-reset");
    widgetLinks.forEach(fixCheckbox);
  }

  Drupal.behaviors.facetsCheckboxReset = {
    attach() {
      speedwayFacetsPrepCheckboxes();
    }
  };
})(Drupal);
