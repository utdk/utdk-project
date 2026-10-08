<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'utexas_flex_list' formatter.
 */
#[FieldFormatter(
  id: 'utexas_flex_list_default',
  label: new TranslatableMarkup('Title/body list'),
  field_types: ['utexas_flex_list']
)]
class UTexasFlexListDefaultListFormatter extends UTexasFlexListFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'heading_level' => 'dl',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['heading_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Heading level'),
      '#options' => [
        'h2' => $this->t('h2'),
        'h3' => $this->t('h3'),
        'h4' => $this->t('h4'),
        'h5' => $this->t('h5'),
        'h6' => $this->t('h6'),
        'dl' => $this->t('Description List'),
      ],
      '#default_value' => $this->getSetting('heading_level'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();
    if (!empty($settings['heading_level'])) {
      $summary[] = $this->t('Heading level: @display', ['@display' => $settings['heading_level']]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $element) {
      if (is_numeric($delta)) {
        $elements[$delta]['heading_level'] = ['#plain_text' => $this->getSetting('heading_level')];
      }
    }
    return $elements;
  }

}
