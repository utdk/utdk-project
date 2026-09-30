<?php

namespace Drupal\utexas_site_announcement\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Provides an announcement color scheme form.
 */
class UTexasAnnouncementColorSchemeForm extends EntityForm {

  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $utexas_site_announcement = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $utexas_site_announcement->label(),
      '#description' => $this->t("Label for the color scheme."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $utexas_site_announcement->id(),
      '#machine_name' => [
        'exists' => '\Drupal\utexas_site_announcement\Entity\UTexasAnnouncementColorScheme::load',
      ],
      '#disabled' => !$utexas_site_announcement->isNew(),
    ];

    $form['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background Color'),
      '#maxlength' => 255,
      '#default_value' => $utexas_site_announcement->getBackgroundColor(),
      '#description' => $this->t("Provide a hex code with the '#' preceding it, e.g. '#cf7500'"),
      '#required' => TRUE,
    ];

    $form['text_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text Color'),
      '#maxlength' => 255,
      '#default_value' => $utexas_site_announcement->getTextColor(),
      '#description' => $this->t("Provide a hex code with the '#' preceding it, e.g. '#cf7500'"),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Verify that background and text colors appear to be valid hex codes.
    $hex_code_regex = "/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/";
    $background_color = $form_state->getValue('background_color');
    $text_color = $form_state->getValue('text_color');
    $color_fields_to_validate = [
      'background_color' => $background_color,
      'text_color' => $text_color,
    ];
    foreach ($color_fields_to_validate as $field => $val) {
      if (!preg_match($hex_code_regex, $val)) {
        // The value is not a valid hex code.
        $form_state->setErrorByName($field, $this->t('%hex is an invalid hex code.', ['%hex' => $val]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $utexas_site_announcement = $this->entity;
    $status = $utexas_site_announcement->save();
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created configuration for %label.', [
          '%label' => $utexas_site_announcement->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved configuration for %label.', [
          '%label' => $utexas_site_announcement->label(),
        ]));
    }
    $form_state->setRedirectUrl($utexas_site_announcement->toUrl('collection'));
    Cache::invalidateTags(['utexas_site_announcement']);
  }

}
