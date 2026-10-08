<?php

namespace Drupal\utexas_layouts\Traits;

use Drupal\Core\Form\FormStateInterface;

/**
 * Trait for layouts with configurable widths.
 */
trait MultiWidthLayoutTrait {

  /**
   * {@inheritdoc}
   */
  public function multiWidthConfiguration() {
    $width_classes = array_keys($this->getWidthOptions());
    return [
      'column_widths' => array_shift($width_classes),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function multiWidthConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['column_widths'] = [
      '#type' => 'select',
      '#title' => $this->t('Column proportions'),
      '#default_value' => $this->configuration['column_widths'],
      '#options' => $this->getWidthOptions(),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateMultiWidthConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitMultiWidthConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['column_widths'] = $form_state->getValue('column_widths');
  }

  /**
   * {@inheritdoc}
   */
  public function buildMultiWidth(&$build) {
    $build['#attributes']['class'][] = 'layout';
    $build['#attributes']['class'][] = $this->getPluginDefinition()->getTemplate();
    $build['#attributes']['class'][] = $this->getPluginDefinition()->getTemplate() . '--' . $this->configuration['column_widths'];
  }

}
