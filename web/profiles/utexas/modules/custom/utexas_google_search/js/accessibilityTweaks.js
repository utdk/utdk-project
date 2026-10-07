/**
 * @file
 * Adds accessibility enhancements to Google search results.
 */

(function utexasGoogleSearchAccessibility(Drupal) {
  "use strict";

  function adjustSearchResultsAccessibility() {
    const results = document.getElementsByClassName("gsc-result-info");
    Object.keys(results).forEach(key => {
      results[key].setAttribute("role", "status");
    });
    // Handle no results.
    const noresults = document.getElementsByClassName("gs-no-results-result");
    Object.keys(noresults).forEach(key => {
      noresults[key].setAttribute("role", "status");
    });
    // Convert title <div> to <h3> for semantic usability.
    const titles = document.querySelectorAll(
      "div.gsc-thumbnail-inside > div.gs-title"
    );
    Object.keys(titles).forEach(key => {
      const header = document.createElement("h3");
      const inner = titles[key].innerHTML;
      header.innerHTML = inner;
      header.classList.add("gs-title");
      titles[key].replaceWith(header);
    });
  }

  function waitForGoogleResults(callback, interval, timeout) {
    const start = Date.now();
    const checkInterval = interval || 500;
    const waitTimeout = timeout || 5000;

    function check() {
      // Check if we have markup indicative of Google Search.
      if (document.getElementsByClassName("gsc-result-info").length) {
        callback();
      } else if (Date.now() - start < waitTimeout) {
        setTimeout(check, checkInterval);
      }
    }

    check();
  }

  function handleSearchResults() {
    waitForGoogleResults(adjustSearchResultsAccessibility);
  }

  Drupal.behaviors.utexasGoogleSearchAccessibility = {
    attach() {
      if (document.readyState !== "loading") {
        handleSearchResults();
      } else {
        window.addEventListener("load", handleSearchResults);
      }
    }
  };
})(Drupal);
