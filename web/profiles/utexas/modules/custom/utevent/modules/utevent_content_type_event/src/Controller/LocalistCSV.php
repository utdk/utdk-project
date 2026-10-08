<?php

namespace Drupal\utevent_content_type_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Localist CSV endpoint.
 *
 * @package Drupal\utevent_content_type_event\Controller
 */
class LocalistCSV extends ControllerBase {

  /**
   * The Controller endpoint.
   */
  public static function endpoint(Request $request) {
    $config = \Drupal::config('utevent_content_type_event.config');
    $department = $config->get('department') ?? '';
    $base_url = \Drupal::service('router.request_context')->getCompleteBaseUrl();
    $view = \Drupal::entityTypeManager()
      ->getStorage('view')
      ->load('utevent_localist')
      ->getExecutable();
    $view->initDisplay();
    $view->execute();
    foreach ($view->result as $rid => $row) {
      $rows[$rid][] = $view->field['title']->advancedRender($row)->__toString();
      $rows[$rid][] = $view->field['field_utevent_body']->advancedRender($row)->__toString();
      $rows[$rid][] = $view->field['field_utevent_datetime_value']->advancedRender($row);
      $rows[$rid][] = $view->field['field_utevent_datetime_value_1']->advancedRender($row);
      $rows[$rid][] = $view->field['field_utevent_datetime_end_value']->advancedRender($row);
      $rows[$rid][] = $view->field['field_utevent_datetime_end_value_1']->advancedRender($row);
      $rows[$rid][] = $view->field['field_utevent_location']->advancedRender($row);
      $rows[$rid][] = $view->field['field_utevent_tags']->advancedRender($row);
      $rows[$rid][] = $view->field['view_node']->advancedRender($row);
      $rows[$rid][] = $view->field['field_utexas_media_image']->advancedRender($row) ? $base_url . '/' . $view->field['field_utexas_media_image']->advancedRender($row)->__toString() : '';
      $rows[$rid][] = $view->field['field_utexas_media_image_1']->advancedRender($row);
      $rows[$rid][] = $department;
      $unlisted = $view->field['field_utevent_localist_unlisted']->advancedRender($row) ?? NULL;
      if ($unlisted !== NULL && !is_string($unlisted)) {
        $unlisted = $unlisted->__toString();
      }
      $rows[$rid][] = (bool) $unlisted ? 'Unlisted' : 'Public';
    }
    $file_directory = 'public://';
    $file_name = 'localist.csv';
    $csv_file_path = $file_directory . $file_name;
    $csv_file = fopen($csv_file_path, 'w');
    $header = [
      'Title',
      'Description',
      'Date From',
      'Start Time',
      'Date To',
      'End Time',
      'Location',
      'Tags',
      'Event Website',
      'Photo URL',
      'Photo Description',
      'Department',
      'Visibility',
    ];
    fputcsv($csv_file, $header, ',', '"', '\\');
    foreach ($rows as $row) {
      fputcsv($csv_file, $row, ',', '"', '\\');
    }
    // Close the stream.
    fclose($csv_file);
    $response = new BinaryFileResponse($csv_file_path);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="localist.csv"');
    return $response;
  }

}
