<?php

namespace Drupal\utevent_content_type_event\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\utevent\Form\BaseConfigurationForm;

/**
 * Supplement form UI to add setting Localist departments.
 */
class ContentConfig extends BaseConfigurationForm {

  /**
   * The actual form elements.
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
    $config = $this->configFactory->get('utevent_content_type_event.config');
    $default_department = $config->get('department');
    $form['department'] = [
      '#title' => $this->t('Event department name'),
      '#type' => 'textfield',
      '#placeholder' => 'e.g., "Dell Medical School", "Department of Student Affairs"',
      '#default_value' => $default_department ?? '',
      '#description' => $this->t('Upcoming events are available for display on <a href=":link">:link</a>. Specify a department name to be associated with this site\'s events. <ul><li>The name entered must match an existing department name listed <a href=":departments">here</a>.</li><li>If there is no appropriate department, use the <a href=":survey">calendar.utexas.edu support form</a>.</li><li>To have your events listed on calendar.utexas.edu, use the <a href=":survey">calendar.utexas.edu support form</a>, providing the link to the site event feed, <code>:feed</code></li></ul>', [
        ':feed' => \Drupal::request()->getSchemeAndHttpHost() . '/events/localist.csv',
        ':link' => 'https://calendar.utexas.edu',
        ':departments' => 'https://docs.google.com/spreadsheets/d/1HBoBuMMNjgkJYOm7fI4CpYNrHpbSL61vJMEvXTG7DIY/edit?gid=31309768#gid=31309768',
        ':survey' => 'https://utexas.qualtrics.com/jfe/form/SV_0jNfR9GXvYv69V4',
      ]),
    ];
    $automatically_archive_on = $config->get('automatically_archive_on');
    $form['automatically_archive_on'] = [
      '#title' => $this->t('Automatically archive past events'),
      '#type' => 'checkbox',
      '#default_value' => $automatically_archive_on ?? FALSE,
      '#description' => $this->t('Set past events to archived state after a specified number of days in the past.'),
    ];
    $automatically_archive_days = $config->get('automatically_archive_days');
    $form['automatically_archive_days'] = [
      '#title' => $this->t('Archive events whose end date is older than this many days in the past.'),
      '#type' => 'number',
      '#min' => '1',
      '#max' => '365',
      '#default_value' => $automatically_archive_days ?? '30',
      '#states' => [
        'disabled' => [
          ':input[name="automatically_archive_on"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['#submit'][] = [$this, 'submitContentConfig'];
  }

  /**
   * Extended submit callback.
   */
  public function submitContentConfig(&$form, FormStateInterface $form_state) {
    $department = $form_state->getValue('department');
    $automatically_archive_on = $form_state->getValue('automatically_archive_on');
    $automatically_archive_days = $form_state->getValue('automatically_archive_days');
    $config = $this->configFactory->getEditable('utevent_content_type_event.config');
    $config->set('department', $department);
    $config->set('automatically_archive_on', $automatically_archive_on);
    $config->set('automatically_archive_days', $automatically_archive_days);
    $config->save();
  }

}
