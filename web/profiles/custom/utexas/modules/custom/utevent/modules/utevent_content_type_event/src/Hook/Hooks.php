<?php

namespace Drupal\utevent_content_type_event\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utevent_content_type_event\EventContentTypeHelper;
use Drupal\utevent_content_type_event\Form\ContentConfig;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public function cron() {
    // Option to archive past events.
    $config_id = 'utevent_content_type_event.config';
    $config = \Drupal::config($config_id);
    if ($config->get('automatically_archive_on')) {
      $day_offset = $config->get('automatically_archive_days') ?? '30';
      // Get current time minus the number of days (in seconds) to delay.
      // e.g., only archive if the event end value is less than today minus
      // N days.
      $time_offset = time() - $day_offset * 86400;
      \Drupal::logger('utevent_content_type_event')->notice('Checking for past events to archive...');
      $published_events = \Drupal::entityTypeManager()->getStorage('node')
        ->loadByProperties(['type' => 'utevent_event', 'status' => 1]);
      /** @var \Drupal\entity\node $event */
      foreach ($published_events as $event) {
        $instances = $event->get('field_utevent_datetime')->getValue();
        $has_upcoming = FALSE;
        foreach ($instances as $instance) {
          // If the end value is greater than the time offset, there is still
          // an upcoming event, so don't archive.
          if ($instance['end_value'] > $time_offset) {
            $has_upcoming = TRUE;
          }
        }
        if (!$has_upcoming) {
          \Drupal::logger('utevent_content_type_event')->notice('Archiving event "%event"', ['%event' => $event->getTitle()]);
          $event->set('moderation_state', 'archived');
          $event->setUnpublished()->save();
        }
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'node__utevent_event' => [
        'template' => 'node--utevent-event',
        'base hook' => 'node',
      ],
      'node__utevent_event__full' => [
        'template' => 'node--utevent-event--full',
        'base hook' => 'node',
      ],
      'node__utevent_event__teaser' => [
        'template' => 'node--utevent-event--teaser',
        'base hook' => 'node',
      ],
      'field__node__utevent_event' => [
        'template' => 'field--node--utevent-event',
        'base hook' => 'field',
      ],
      'field__node__field_utevent_main_media' => [
        'template' => 'field--node--field-utevent-main-media',
        'base hook' => 'field',
      ],
      'field__node__field_utevent_status' => [
        'template' => 'field--node--field-utevent-status',
        'base hook' => 'field',
      ],
      'field__node__field_utevent_body' => [
        'template' => 'field--node--field-utevent-body',
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_field')]
  public function themeSuggestionsField(array $variables) {
    $suggestions = [];

    $element = $variables['element'];

    $field_name = $element['#field_name'];
    $view_mode = $element['#view_mode'];
    $entity_type = $element['#entity_type'];
    $bundle = $element['#bundle'];

    $suggestions[] = 'field__' . $field_name . '__' . $view_mode;
    $suggestions[] = 'field__' . $entity_type . '__' . $field_name . '__' . $view_mode;
    $suggestions[] = 'field__' . $entity_type . '__' . $bundle . '__' . $field_name . '__' . $view_mode;

    return $suggestions;
  }

  /**
   * Helper function to add the "Standard workflow" to the content type.
   */
  public static function addStandardWorkflow() {
    $config_id = 'workflows.workflow.standard_workflow';
    $config = \Drupal::service('config.factory')->getEditable($config_id);
    if (is_null($config->get('id'))) {
      \Drupal::logger('utevent_content_type_event')->notice('Standard workflow not found. Skipping...');
      // This site does not use the standard_workflow. Move on.
      return;
    }
    \Drupal::logger('utevent_content_type_event')->notice('Standard workflow found. Updating...');
    $type_settings = $config->get('type_settings');
    $new_type_settings = $type_settings;
    $new_type_settings['entity_types']['node'][] = 'utevent_event';
    $config->set('type_settings', $new_type_settings);
    $config->save();
    // A cache rebuild is required for this configuration change to show.
    drupal_flush_all_caches();
  }

  /**
   * Implements hook_preprocess_node().
   */
  #[Hook('preprocess_node')]
  public function preprocessNode(&$variables) {
    $node = $variables['elements']['#node'];
    $type = $node->getType();

    // If the event is cancelled, hide "Add to calendar" functionality (#347).
    if ($type === 'utevent_event') {
      if ($node->hasField('field_utevent_status')) {
        $utevent_status = $node->get('field_utevent_status')->getString();
        if ($utevent_status === 'EventCancelled') {
          $variables['canceled'] = TRUE;
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(&$variables, $hook) {
    // Convert taxonomy terms to link to `/events?<term>.
    if (\Drupal::moduleHandler()->moduleExists('utevent_view_listing_page') !== FALSE) {
      switch ($variables['element']['#field_name']) {
        case 'field_utevent_location':
          foreach ($variables['items'] as &$item) {
            if (isset($item['content']['#entity'])) {
              unset($item['content']['#plain_text']);
              $item['content']['#markup'] = EventContentTypeHelper::prepareEventTaxonomy($item['content']['#entity'], 'location');
            }
          }
          break;

        case 'field_utevent_tags':
          foreach ($variables['items'] as &$item) {
            if (isset($item['content']['#entity'])) {
              unset($item['content']['#plain_text']);
              $item['content']['#markup'] = EventContentTypeHelper::prepareEventTaxonomy($item['content']['#entity'], 'type');
            }
          }
          break;

        default:
          return;
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter() for the general config form.
   */
  #[Hook('form_utevent_general_config_alter')]
  public function formUteventGeneralConfigAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Alter the general config form to include the content configuration.
    \Drupal::classResolver(ContentConfig::class)->alterForm($form, $form_state, $form_id);
  }

}
