<?php

namespace Drupal\utevent_view_listing_page\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utevent_view_listing_page\Form\ListingPageConfig;
use Drupal\views\ViewExecutable;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_preprocess_views_view_field().
   */
  #[Hook('preprocess_views_view_fields')]
  public function preprocessViewsViewFields(&$variables) {
    $view = $variables['view'];
    if ($view->id() !== 'utevent_listing_page') {
      return;
    }

    if (!isset($variables['fields']['field_utevent_datetime_1'])) {
      return;
    }

    // Strip tags to get raw rendered value.
    $variables['fields']['field_utevent_datetime_1']->content = strip_tags($variables['fields']['field_utevent_datetime_1']->content);

    // Do not display datetime narrative for non-recurring events.
    /** @var \Drupal\views\Plugin\views\field\EntityField $field_handler */
    $field_handler = $variables['fields']['field_utevent_datetime_1']->handler;
    $field_items = $field_handler->getItems($variables['row']);
    $field_item = reset($field_items);
    /** @var \Drupal\smart_date\Plugin\Field\FieldType\SmartDateItem $smart_date_item */
    $smart_date_item = $field_item['raw'];
    $smart_date_item_values = $smart_date_item->getValue();
    if ($smart_date_item_values['rrule'] === NULL) {
      unset($variables['fields']['field_utevent_datetime_1']);
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter() for the general config form.
   */
  #[Hook('form_utevent_general_config_alter')]
  public function formUteventGeneralConfigAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Alter the general config form to include the listing page title.
    \Drupal::classResolver(ListingPageConfig::class)->alterForm($form, $form_state, $form_id);
  }

  /**
   * Implements hook_views_pre_view().
   */
  #[Hook('views_pre_view')]
  public function viewsPreView(ViewExecutable $view, $display_id, array &$args) {
    $config = \Drupal::config('utevent_view_listing_page.config');
    $display_past = $config->get('display_past_events');
    $display_calendar = $config->get('display_calendar');
    switch ($view->id()) {
      case 'utevent_listing_page':
        if (!$display_calendar) {
          // Remove the 'Calendar' button.
          if ($display_id == 'upcoming') {
            $view->removeHandler('upcoming', 'header', 'area_text_custom_2');
          }
          if ($display_id == 'past') {
            $view->removeHandler('past', 'header', 'area_text_custom_1');
          }
        }
        if ($display_id == 'upcoming') {
          if ($display_past) {
            // Remove the display that doesn't show the 'Past Events' button.
            $view->removeHandler('upcoming', 'header', 'area_text_custom_1');
          }
          else {
            // Remove both displays that show the 'Past Events' button.
            $view->removeHandler('upcoming', 'header', 'area_text_custom_1');
            $view->removeHandler('upcoming', 'header', 'area_text_custom');
          }
        }
        break;

      case 'utevent_calendar_page':
        if ($display_past) {
          // Remove the display that doesn't show the 'Past Events' button.
          $view->removeHandler('calendar', 'header', 'area_text_custom_1');
        }
        else {
          // Remove the display that does show the 'Past Events' button.
          $view->removeHandler('calendar', 'header', 'area_text_custom');
        }
        break;

      default:
        break;
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_views_exposed_form_alter')]
  public function formViewsExposedFormAlter(array &$form, FormStateInterface $form_state, $form_id) {
    $view = $form_state->getStorage('view');
    if (in_array($view['view']->id(), ['utevent_listing_page', 'utevent_calendar_page'])) {
      $location_count = isset($form['location']) ? count($form['location']['#options']) : 0;
      $tag_count = isset($form['type']) ? count($form['type']['#options']) : 0;
      if ($location_count <= 1) {
        $form['location']['#access'] = FALSE;
      }
      if ($tag_count <= 1) {
        $form['type']['#access'] = FALSE;
      }
      if ($location_count <= 1 && $tag_count <= 1) {
        $form['actions']['#access'] = FALSE;
      }
    }
  }

  /**
   * Implements hook_js_settings_alter().
   *
   * Prepend date to event title for display in FullCalendar views.
   */
  #[Hook('js_settings_alter')]
  public function jsSettingsAlter(array &$settings) {
    if (!isset($settings['fullCalendarView'][0]['calendar_options'])) {
      return;
    }
    $calendar_options = json_decode($settings['fullCalendarView'][0]['calendar_options'], TRUE);
    foreach ($calendar_options['events'] as $key => $event) {
      $title = $event['title'];
      $start = date('F d', strtotime($event['start']));
      $calendar_options['events'][$key]['title'] = '<span class="visually-hidden">' . $start . ':</span> ' . $title;
    }
    $settings['fullCalendarView'][0]['calendar_options'] = json_encode($calendar_options);
  }

}
