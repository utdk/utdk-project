/**
 * @file
 * Client-side behavior to restrict datetime inputs to top-of-hour.
 */

(function(Drupal) {
  "use strict";

  Drupal.behaviors.datetimeHourRestrict = {
    attach(context) {
      /**
       * Restrict time input to top of the hour (00 minutes).
       *
       * The time input format is HH:mm or HH:mm:ss.
       * Normalizes to HH:00 or HH:00:00 respectively.
       *
       * @param {HTMLInputElement} input - The time input element.
       */
      const restrictToHour = input => {
        const { value } = input;

        if (!value) {
          return;
        }

        // Time format is HH:mm or HH:mm:ss. Match it and extract the hour.
        const match = value.match(/^(\d{2}):\d{2}(?::\d{2})?$/);

        if (match) {
          // Reconstruct with :00 for minutes (and :00 for seconds if present).
          // Format: HH:mm or HH:mm:ss → HH:00 or HH:00:00
          const restrictedValue =
            value.includes(":") && value.split(":").length === 3
              ? `${match[1]}:00:00`
              : `${match[1]}:00`;

          if (value !== restrictedValue) {
            input.value = restrictedValue;
            // Trigger change event so form knows the value changed.
            input.dispatchEvent(new Event("change", { bubbles: true }));
          }
        }
      };

      // Look for the time inputs that should be restricted.
      // Match: on[time] (add form) and date[time] (reschedule form).
      const timeInputs = context.querySelectorAll(
        'input[name="on[time]"], input[name="date[time]"]'
      );

      timeInputs.forEach(input => {
        // Set step attribute to 3600 (1 hour).
        input.setAttribute("step", "3600");
        input.setAttribute("data-hour-only", "true");

        // Bind events for real-time normalization.
        ["change", "blur", "input", "keyup"].forEach(eventName => {
          input.addEventListener(eventName, () => restrictToHour(input));
        });

        input.addEventListener("paste", () => {
          setTimeout(() => {
            restrictToHour(input);
          }, 50);
        });
      });
    }
  };
})(Drupal);
