# Scheduled Transitions - Hour Only Restriction

Restricts scheduled content moderation transitions to top-of-hour times (00 minutes, 00 seconds).

## How It Works

The module provides **three layers of validation**:

1. The module enables scheduled transitions for all existing node types on a site
2. **Client-side validation (JavaScript)** — When an editor selects a time, minutes and seconds are automatically set to 00:00.
3. **HTML5 attribute** — The datetime input's `step` attribute is set to 3600 seconds (1 hour), which some browsers respect.
4. **Server-side validation** — The form validation handler rejects any submission where minutes or seconds are not 00.

This ensures that even if an editor somehow bypasses the client-side behavior (e.g., via browser dev tools or direct API calls), the server rejects invalid times.

## Installation

1. Enable the module:
   ```bash
   drush en utexas_scheduled_transitions
   ```

## Usage

Once enabled, the module automatically restricts the "Schedule transition" form on all scheduled transitions. Editors will see:

- A datetime picker that automatically rounds to the top of the hour
- A validation error if they try to submit with non-zero minutes/seconds

## File Structure

```
utexas_scheduled_transitions/
├── utexas_scheduled_transitions.info.yml     # Module metadata
├── utexas_scheduled_transitions.install      # Registers existing bundles & defaults on install
├── utexas_scheduled_transitions.libraries.yml # JavaScript library definition
├── utexas_scheduled_transitions.services.yml # Disables procedural hook scanning
├── js/
│   └── datetime-hour-restrict.js             # Client-side behavior
├── src/
│   ├── Hook/
│   │   └── Hooks.php                         # Hook implementations (form_alter, node_type_insert, validation)
│   ├── TransitionsHelper.php                 # Registers a bundle with Scheduled Transitions & grants permissions
│   └── Plugin/Validation/Constraint/
│       ├── HourOnlyTimeConstraint.php        # Constraint definition
│       └── HourOnlyTimeConstraintValidator.php # Constraint validator
└── README.md                                 # This file
```

## Testing

To test the module:

1. Enable it on your site
2. Navigate to a node's "Scheduled transitions" tab
3. Click "Add" to schedule a transition
4. Select a datetime with non-zero minutes (e.g., 2:15 PM)
5. Submit the form
6. Observe:
   - JavaScript automatically corrects to 2:00 PM (client-side)
   - If JS is disabled, server validation rejects with an error message

## Notes

The form_id detection uses pattern matching to handle dynamically generated form IDs:
- Matches any form with 'scheduled_transition' and 'add' in the form_id
- This catches variations like `scheduled_transition_add_form`, `node_page_scheduled_transitions_add_form`, etc.
