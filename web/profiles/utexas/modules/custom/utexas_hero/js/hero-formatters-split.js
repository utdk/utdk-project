(function ($, Drupal) {
  "use strict";

  /**
   * @file
   *
   * Hero formatter split progressive enhancement definition.
   *
   * To facilitate the usage of more than 10 image styles/view modes for the
   * hero, we create 2 different HTML select elements: one for the style,
   * and one for anchor positioning. The combination of both decide
   * which of the original image styles gets picked. This choice then
   * modifies the now hidden original select element. Drupal then saves
   * things as it normally would. This is purely a user facing change,
   * not a data storage change.
   */

  /**
   * Define a Drupal behavior to create custom hero select elements.
   */
  Drupal.behaviors.utexasHeroCustomSelectors = {
    attach: function (context, settings) {
      // 1. Hero styles may be accessed via field formatter or view mode.
      // Determine which of these is active, or exit if neither.
      var form_mode = getFormMode();
      if (form_mode === null) {
        return;
      }
      // Determine original select HTML element, depending on the form mode.
      var form_mode_dom =
      (form_mode === "formatter" ? ".js-form-item-settings-formatter-type"
      : ".js-form-item-settings-view-mode");
      // If there is no hero default selector, we are not on a hero
      // configuration form.
      $(form_mode_dom).each(function() {
        if ($(form_mode_dom)
        .find("select option[value=utexas_hero_1_left]").length === null) {
          return;
        }
      });
      // Set the formatter type which differ between formatter and view mode.
      var formatter_type =
      (form_mode === "view_mode" ? "settings[" + form_mode + "]"
      : "settings[" + form_mode + "][type]");
      // Set jquery element for original hidden select element.
      var original_select_element = $("select[name='" + formatter_type + "']");
      var current_formatter_style = original_select_element.val();
      // Since formatter and view mode don't have a consistent value for the
      // default select element, we attempt to set the value to "default" if the
      // current style is indeed set to any of the default values.
      var default_style = ((current_formatter_style === undefined
      || current_formatter_style === "full"
      || current_formatter_style === "utexas_hero") ? "default"
      : current_formatter_style);
      // Access helper function to split the current formatter into custom
      // style and anchor position.
      var style_and_anchor = getStyleAndAnchorValue(default_style);

      // 2. Hide the view mode selector.
      $(form_mode_dom).hide();

      // 3. Move the new selectors to be adjacent to dynamic fields.
      var selector = document.getElementById('edit-utexas-hero-style-selector--wrapper');
      var anchor = document.getElementById('edit-utexas-hero-anchor--wrapper');
      var hero_selector_target = document.getElementById('hero_selector_target');
      if (hero_selector_target) {
        hero_selector_target.appendChild(selector);
        hero_selector_target.appendChild(anchor);
      }

      // 4. Set values of custom radio HTML elements.
      // Update the value in the style and anchor radio elements.
      if( default_style !== "default") {
        $("input[name='utexas_hero_style_selector'][value=\"" + style_and_anchor.style + "\"]").attr("checked","checked");
        $("input[name='utexas_hero_anchor'][value=\"" + style_and_anchor.anchor + "\"]").attr("checked","checked");
       }
       else {
        $("input[name='utexas_hero_style_selector'][value='default']").attr("checked","checked");
        $("input[name='utexas_hero_anchor'][value='center']").attr("checked","checked");
       }

      // Convert default_style if "default" is set but we use a formatter form
      // mode.
      default_style = (form_mode === "formatter"
      && default_style === "default" ? "utexas_hero"
      : default_style);

      // Update the values in the original hidden select element.
      original_select_element.val(default_style);
      // Toggle anchor select element if current hero don't use anchor.
      toggleAnchorSelectElement(default_style);

      // 5. Watch for changes on the custom select elements, and keep
      // the original select element in sync.

      // Watch the hero style custom select element.
      $("input[name='utexas_hero_style_selector']", context).change(function() {

        // Get the hero style and convert to utexas_hero if form mode is set
        // to formatter.
        var hero_style = (form_mode === "formatter"
        && $("input[name=utexas_hero_style_selector]:checked").val() === "default"
        ? "utexas_hero"
        : $("input[name=utexas_hero_style_selector]:checked").val());

        updateSelectors(original_select_element, "", hero_style);
      });

      // Watch the hero anchor custom select element.
      $("input[name='utexas_hero_anchor']", context).change(function() {

        var anchor = "_" + $("input[name='utexas_hero_anchor']:checked")
        .val();

        updateSelectors(original_select_element, anchor, "");
      });
    }
  };

  /**
   * Update the values and state of the select elements.
   *
   * @param {string} original_select_element This variable defines the HTML
   *    original hidden select element.
   * @param {string} anchor (Optional) The anchor value which could be "center",
   *    "left" or "right". Also used to validate the selector visibility.
   * @param {string} hero_style (Optional) The hero style that will be massaged
   *    and define the official formatter/view mode value. Will also be used to
   *    validate and massage the hero_style value.
   */
  function updateSelectors(original_select_element, anchor="", hero_style="") {
    // If no hero style passed as argument, get the current value.
    hero_style = ((hero_style === "")
    ? $("input[name='utexas_hero_style_selector']:checked").val()
    : hero_style);
    // If no anchor passed as argument, get the current value
    anchor = ((anchor === "")
    ? "_" + $("input[name='utexas_hero_anchor']:checked").val()
    : anchor);
    // Massage the custom anchor position value.
    toggleAnchorSelectElement(hero_style);
    var disabled_anchor = ($("input[name='utexas_hero_anchor']").prop("disabled") ? true
    : false);
    // Don't add suffix if anchor is center or anchor select is disabled.
    anchor = ((anchor === "_center" || disabled_anchor) ? ""
    : anchor);
    // Update original formatter select element value.
    original_select_element.val(hero_style + anchor);
  }

  /**
   * Return either formatter or view_mode depending on context the
   * utexas_hero field is being used.
   * This will vary between node, inline block, reusable block, etc.
   * @return {string} Returns the form_mode if valid, or null if not
   */
  function getFormMode() {
    var form_mode = null;
    // Check if the layout sidebar has a formatter or view mode, if not, exit.
    if ($("#drupal-off-canvas").has(".js-form-item-settings-formatter-type").length
    || $(".layout-builder-configure-block").has(".js-form-item-settings-formatter-type").length) {
      form_mode = "formatter";
    }
    else if ($("#drupal-off-canvas").has(".js-form-item-settings-view-mode").length
    || $(".layout-builder-configure-block").has(".js-form-item-settings-view-mode").length) {
      form_mode = "view_mode";
    }
    else if ($(".block-form").has(".js-form-item-settings-view-mode").length
    || $(".layout-builder-configure-block").has(".js-form-item-settings-view-mode").length) {
      form_mode = "view_mode";
    }
    return form_mode;
  }

  /**
   * Return the current style and anchor values from the default Drupal
   * select element.
   *
   * @param {string} default_style Should contain
   *    a value similar to "utexas_hero_X_[anchor]" where X is a number that
   *    defines the style number to select in the custom hero style selector.
   *    And an optional anchor that defines the value of the anchor selector.
   * @return {array} The style and anchor settings.
   */
  function getStyleAndAnchorValue(default_style) {
    // Split and return an array.
    var default_style_split = default_style.split("utexas_hero_");
    // Will return the style and anchor if found, e.g. ['1', 'left'],
    // or 'utexas_hero'.
    var style_and_anchor = (default_style_split[1] !== undefined
    ? default_style_split[1].split("_")
    : "utexas_hero");
    var anchorDefined = style_and_anchor[1] !== undefined;
    return {
      "style" : "utexas_hero_" + style_and_anchor[0],
      "anchor" : (anchorDefined ? style_and_anchor[1]
      : "center"),
    };
  }

  /**
   * Toggle anchor select element state depending on selected hero style.
   *
   * @param {string} hero_style The current hero style.
   */
  function toggleAnchorSelectElement(hero_style) {
    if (hero_style === "default" || hero_style === "utexas_hero") {
      disableField('settings[block_form][field_block_hero][0][heading]');
      disableField('settings[block_form][field_block_hero][0][subheading]');
      enableField('settings[block_form][field_block_hero][0][caption]');
      enableField('settings[block_form][field_block_hero][0][credit]');
      disableField('settings[block_form][field_block_hero][0][cta][link][uri]');
      disableField('settings[block_form][field_block_hero][0][cta][link][title]');
    }
    else if (hero_style === "utexas_hero_2") {
      enableField('settings[block_form][field_block_hero][0][heading]');
      disableField('settings[block_form][field_block_hero][0][subheading]');
      disableField('settings[block_form][field_block_hero][0][caption]');
      disableField('settings[block_form][field_block_hero][0][credit]');
      enableField('settings[block_form][field_block_hero][0][cta][link][uri]');
      enableField('settings[block_form][field_block_hero][0][cta][link][title]');
    }
    else {
      // Hero Styles 1, 3, 4, and 5.
      enableField('settings[block_form][field_block_hero][0][heading]');
      enableField('settings[block_form][field_block_hero][0][subheading]');
      disableField('settings[block_form][field_block_hero][0][caption]');
      disableField('settings[block_form][field_block_hero][0][credit]');
      enableField('settings[block_form][field_block_hero][0][cta][link][uri]');
      enableField('settings[block_form][field_block_hero][0][cta][link][title]');
    }

    // Disable anchor select element if using default or style 4.
    if (hero_style === "default" || hero_style === "utexas_hero" || hero_style === "utexas_hero_4") {
      $("input[name='utexas_hero_anchor']").prop("disabled", true);
    }
    else {
      $("input[name='utexas_hero_anchor']").removeAttr("disabled");
    }
  }

  /**
   * Disable a field based on the field name
   * @param {string} name The field name as shown in the DOM.
   */
  function disableField(name) {
    let targets = document.getElementsByName(name);
    for (let i = 0; i < targets.length; i++) {
      targets[i].disabled = true;
      targets[i].title = 'This field does not display with the currently selected Hero style.';
    }
  }

  /**
   * Enable a field based on the field name
   * @param {string} name The field name as shown in the DOM.
   */
  function enableField(name) {
    let targets = document.getElementsByName(name);
    for (let i = 0; i < targets.length; i++) {
      targets[i].disabled = false;
    }
  }

})(jQuery, Drupal);
