<?php

namespace Drupal\utevent_content_type_event\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\smart_date\SmartDateTrait;
use Drupal\smart_date_recur\Plugin\Field\FieldFormatter\SmartDateRecurrenceFormatter;

/**
 * Plugin for a recurrence-optimized formatter for 'smartdate' fields.
 */
#[FieldFormatter(
  id: 'utevent_smartdate_recurring',
  label: new TranslatableMarkup('UT Event Recurring'),
  field_types: ['smartdate']
)]
class UtEventSmartDateRecurrenceFormatter extends SmartDateRecurrenceFormatter {

  use SmartDateTrait;

  /**
   * {@inheritdoc}
   *
   * Override method from trait. This is not useless.
   */
  public function viewElements(FieldItemListInterface $items, $langcode, $format = '') {
    return parent::viewElements($items, $langcode);
  }

  /**
   * Creates an AP-style date as a string.
   *
   * Override method from trait.
   *
   * @param object $start_ts
   *   A timestamp.
   * @param object $end_ts
   *   A timestamp.
   * @param array $settings
   *   The formatter settings.
   * @param string|null $timezone
   *   An optional timezone override.
   * @param string $return_type
   *   An optional parameter to force the return value to be a string.
   *
   * @return string|array
   *   A formatted date range using the chosen format.
   */
  public static function formatSmartDate($start_ts, $end_ts, mixed $settings = [], $timezone = NULL, $return_type = '') {

    $date_options_all = [
      'always_display_year' => TRUE,
      'display_noon_and_midnight' => TRUE,
      'timezone' => $timezone,
      'display_day' => TRUE,
      'display_time' => TRUE,
      'time_before_date' => FALSE,
      'use_all_day' => TRUE,
      'capitalize_noon_and_midnight' => FALSE,
    ];

    $range['start'] = $start_ts;
    $range['end'] = $end_ts;
    $formatted_date = \Drupal::service('date_ap_style.formatter')->formatRange(
      $range,
      $date_options_all,
      NULL,
      Language::LANGCODE_NOT_SPECIFIED,
      'smartdate'
    );

    if ($return_type == 'string') {
      $output = $formatted_date;
    }
    else {
      $output['#markup'] = $formatted_date;
      $output['#attributes']['class'][] = ['smart_date_range'];
    }

    return $output;
  }

  /**
   * Explicitly declare support for the Date Augmenter API.
   *
   * Override method from trait. This is not useless.
   *
   * @return array
   *   The keys and labels for the sets of configuration.
   */
  /* phpcs:ignore */
  public function supportsDateAugmenter() {
    return parent::supportsDateAugmenter();
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySettings($module = NULL) {
    if ($module) {
      if ($module === 'date_augmenter') {
        // Provide fallback date augmenter behavior when used
        // without view mode declaration (Views fields).
        if (!isset($this->thirdPartySettings[$module])) {
          $settings = [
            'instances' => [],
            'rule' => [],
          ];
          return $settings;
        }
      }
      return $this->thirdPartySettings[$module] ?? [];
    }
    return $this->thirdPartySettings;
  }

}
