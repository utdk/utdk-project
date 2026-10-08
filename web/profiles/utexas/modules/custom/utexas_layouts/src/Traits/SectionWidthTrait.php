<?php

namespace Drupal\utexas_layouts\Traits;

use Drupal\Core\Form\FormStateInterface;

/**
 * Control the width of a layout section.
 */
trait SectionWidthTrait {

  /**
   * {@inheritdoc}
   */
  protected function getSectionWidthOptions() {
    return [
      'readable' => 'Readable width',
      'container' => 'Container width',
      'container-fluid' => 'Full width of page',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function sectionWidthConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['section_width'] = [
      '#type' => 'select',
      '#title' => $this->t('Section width'),
      '#default_value' => $this->configuration['section_width'],
      '#options' => $this->getSectionWidthOptions(),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateSectionWidthConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitSectionWidthConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['section_width'] = $form_state->getValue('section_width');
  }

  /**
   * {@inheritdoc}
   */
  public function buildSectionWidth(&$build) {
    $width = $this->configuration['section_width'];
    if ($width === 'readable') {
      $build['#attached']['library'][] = 'utexas_layouts/section-width';
    }
    if ($width === 'container') {
      $width = 'container-xl';
    }
    $build['#attributes']['class'][] = $width;
  }

}
