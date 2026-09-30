<?php

namespace Drupal\utevent_demo_content;

use Drupal\smart_date_recur\Entity\SmartDateRule;

/**
 * PHP array of utevent node data.
 */
class DemoData {

  /**
   * {@inheritdoc}
   */
  public static function loadData() {
    $rrule_values = [
      'freq'        => 'WEEKLY',
      'start'       => strtotime('+2 days midnight + 9 hours + 30 minutes'),
      'end'         => strtotime('+2 days midnight + 10 hours'),
      'entity_type' => 'node',
      'bundle'      => 'utevent_event',
      'field_name'  => 'field_utevent_datetime',
      'parameters'  => 'BYDAY=MO',
      'limit'       => 'COUNT=5',
      'unlimited'   => FALSE,
    ];
    $everyMondayFiveTimes = SmartDateRule::create($rrule_values);
    $instances = $everyMondayFiveTimes->getRuleInstances(NULL);
    $everyMondayFiveTimes->set('instances', ['data' => $instances]);
    $everyMondayFiveTimes->set('rule', 'RRULE:FREQ=WEEKLY;BYDAY=MO;COUNT=5');
    $everyMondayFiveTimes->save();

    $rrule_values = [
      'freq'        => 'DAILY',
      'start'       => strtotime('+2 weeks midnight'),
      'end'         => strtotime('+2 weeks +1 day midnight') - 60,
      'entity_type' => 'node',
      'bundle'      => 'utevent_event',
      'field_name'  => 'field_utevent_datetime',
      'parameters'  => 'BYDAY=MO',
      'limit'       => 'COUNT=7',
      'unlimited'   => FALSE,
    ];
    $sevenDays = SmartDateRule::create($rrule_values);
    $instances = $sevenDays->getRuleInstances(NULL);
    $sevenDays->set('instances', ['data' => $instances]);
    $sevenDays->set('rule', 'RRULE:FREQ=DAILY;BYDAY=MO;COUNT=7');
    $sevenDays->save();

    $data = [
      'Maximal event' => [
        'title' => 'Res maximus',
        'media' => TRUE,
        'field_utevent_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utevent_location' => ['Demo Location 1'],
        'field_utevent_tags' => ['Demo Tag 1', 'Demo Tag 2'],
        'field_utevent_featured' => ['value' => TRUE],
        'field_utevent_status' => ['value' => 'EventMovedOnline'],
        'field_utevent_datetime' => [
          'value' => strtotime('+1 day midnight + 10 hours'),
          'end_value' => strtotime('+1 day midnight + 11 hours'),
          'duration' => '60',
        ],
      ],
      'Minimal event' => [
        'title' => 'Res minimas',
        'field_utevent_body' => [
          'value' => '',
          'summary' => 'Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ],
        'media' => FALSE,
        'field_utevent_status' => ['value' => 'EventScheduled'],
        'field_utevent_datetime' => [
          'value' => strtotime('+3 days midnight + 18 hours'),
          'end_value' => strtotime('+3 days midnight + 20 hours'),
          'duration' => '120',
        ],
      ],
      'Single occurrence, 1 week in the future, spanning 1 hour' => [
        'title' => 'Eventu futuri',
        'media' => TRUE,
        'field_utevent_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utevent_location' => ['Demo Location 1'],
        'field_utevent_tags' => ['Demo Tag 1'],
        'field_utevent_status' => ['value' => 'EventScheduled'],
        'field_utevent_datetime' => [
          'value' => strtotime('+1 week midnight + 14 hours'),
          'end_value' => strtotime('+1 week midnight + 15 hours'),
          'duration' => '60',
        ],
      ],
      'Single occurrence, 1 week in the past, spanning 1 hour' => [
        'title' => 'Eventus praeteriti',
        'media' => TRUE,
        'field_utevent_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utevent_location' => ['Demo Location 2'],
        'field_utevent_tags' => ['Demo Tag 2'],
        'field_utevent_status' => ['value' => 'EventScheduled'],
        'field_utevent_datetime' => [
          'value' => strtotime('-1 week midnight + 12 hours'),
          'end_value' => strtotime('-1 week midnight + 13 hours'),
          'duration' => '60',
        ],
      ],
      'Single occurrence, 2 days in the future, all day' => [
        'title' => 'Tota diem',
        'media' => TRUE,
        'field_utevent_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Dolore magna aliqua sed do eiusmod tempor incididunt ut labore.',
          'format' => 'flex_html',
        ],
        'field_utevent_location' => ['Demo Location 1'],
        'field_utevent_tags' => ['Demo Tag 1'],
        'field_utevent_status' => ['value' => 'EventScheduled'],
        'field_utevent_datetime' => [
          'value' => strtotime('+2 days midnight'),
          'end_value' => strtotime('+3 days midnight') - 60,
          'duration' => '1439',
        ],
      ],
      'Recurring, every Monday, 9:30am-10:00am, stopping after 5 times' => [
        'title' => 'Monday repetere quinque',
        'media' => TRUE,
        'field_utevent_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Dolore magna aliqua sed do eiusmod tempor incididunt ut labore.',
          'format' => 'flex_html',
        ],
        'field_utevent_location' => ['Demo Location 1'],
        'field_utevent_tags' => ['Demo Tag 1'],
        'field_utevent_status' => ['value' => 'EventScheduled'],
        'field_utevent_datetime' => [
          0 => [
            'value' => strtotime('next Monday midnight + 9 hours + 30 minutes'),
            'end_value' => strtotime('next Monday midnight + 10 hours'),
            'duration' => '30',
            'rrule' => $everyMondayFiveTimes->id(),
          ],
          1 => [
            'value' => strtotime('next Monday midnight + 1 week + 9 hours + 30 minutes'),
            'end_value' => strtotime('next Monday midnight + 1 week + 10 hours'),
            'duration' => '30',
            'rrule' => $everyMondayFiveTimes->id(),
          ],
          2 => [
            'value' => strtotime('next Monday midnight + 2 weeks + 9 hours + 30 minutes'),
            'end_value' => strtotime('next Monday midnight + 2 weeks + 10 hours'),
            'duration' => '30',
            'rrule' => $everyMondayFiveTimes->id(),
          ],
          3 => [
            'value' => strtotime('next Monday midnight + 3 weeks + 9 hours + 30 minutes'),
            'end_value' => strtotime('next Monday midnight + 3 weeks + 10 hours'),
            'duration' => '30',
            'rrule' => $everyMondayFiveTimes->id(),
          ],
          4 => [
            'value' => strtotime('next Monday midnight + 4 weeks + 9 hours + 30 minutes'),
            'end_value' => strtotime('next Monday midnight + 4 weeks + 10 hours'),
            'duration' => '30',
            'rrule' => $everyMondayFiveTimes->id(),
          ],
        ],
      ],
      'Recurring, starting 2 weeks in the future and going every day for 7 days' => [
        'title' => 'Quotidie septem',
        'media' => TRUE,
        'field_utevent_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Dolore magna aliqua sed do eiusmod tempor incididunt ut labore.',
          'format' => 'flex_html',
        ],
        'field_utevent_location' => ['Demo Location 2'],
        'field_utevent_tags' => ['Demo Tag 2'],
        'field_utevent_status' => ['value' => 'EventScheduled'],
        'field_utevent_datetime' => [
          0 => [
            'value' => strtotime('+2 weeks midnight'),
            'end_value' => strtotime('+2 weeks +1 day midnight') - 60,
            'duration' => '1439',
            'rrule' => $sevenDays->id(),
          ],
          1 => [
            'value' => strtotime('+2 weeks +1 day midnight'),
            'end_value' => strtotime('+2 weeks +2 days midnight') - 60,
            'duration' => '1439',
            'rrule' => $sevenDays->id(),
          ],
          2 => [
            'value' => strtotime('+2 weeks +2 days midnight'),
            'end_value' => strtotime('+2 weeks +3 days midnight') - 60,
            'duration' => '1439',
            'rrule' => $sevenDays->id(),
          ],
          3 => [
            'value' => strtotime('+2 weeks +3 days midnight'),
            'end_value' => strtotime('+2 weeks +4 days midnight') - 60,
            'duration' => '1439',
            'rrule' => $sevenDays->id(),
          ],
          4 => [
            'value' => strtotime('+2 weeks +4 days midnight'),
            'end_value' => strtotime('+2 weeks +5 days midnight') - 60,
            'duration' => '1439',
            'rrule' => $sevenDays->id(),
          ],
          5 => [
            'value' => strtotime('+2 weeks +5 days midnight'),
            'end_value' => strtotime('+2 weeks +6 days midnight') - 60,
            'duration' => '1439',
            'rrule' => $sevenDays->id(),
          ],
          6 => [
            'value' => strtotime('+2 weeks +6 days midnight'),
            'end_value' => strtotime('+2 weeks +7 days midnight') - 60,
            'duration' => '1439',
            'rrule' => $sevenDays->id(),
          ],
        ],
      ],
      'Single occurrence, 3 weeks in the future, spanning two days' => [
        'title' => 'Rem extensam',
        'media' => TRUE,
        'field_utevent_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Dolore magna aliqua sed do eiusmod tempor incididunt ut labore.',
          'format' => 'flex_html',
        ],
        'field_utevent_status' => ['value' => 'EventScheduled'],
        'field_utevent_datetime' => [
          'value' => strtotime('+3 weeks midnight + 10 hours'),
          'end_value' => strtotime('+3 weeks midnight + 1 day + 20 hours'),
          'duration' => 60 * 34,
        ],
      ],
    ];

    return $data;
  }

}
