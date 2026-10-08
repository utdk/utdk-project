(Drupal => {
  "use strict";

  /**
   * @file
   * Support "deep linking" to for Flex List Horizontal tabs.
   */
  Drupal.behaviors.utexasFlexListDeepLink = {
    attach() {
      window.addEventListener("load", () => {
        // eslint-disable-next-line
        const url = location.href.replace(/\/$/, "");
        // eslint-disable-next-line
        if (location.hash) {
          const params = url.split("#");
          const anchor = params[1];
          const anchoredTab = document.querySelector(
            `.utexas-flex-list a[href="#${anchor}"]`
          );
          if (anchoredTab) {
            anchoredTab.click();
            setTimeout(() => {
              anchoredTab.scrollIntoView(true);
            }, 750);
          }
        }
      });
    }
  };
})(Drupal);
