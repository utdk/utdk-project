<?php

namespace Drupal\utexas_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas_layouts\Traits\BackgroundAccentTrait;
use Drupal\utexas_layouts\Traits\BackgroundColorTrait;
use Drupal\utexas_layouts\Traits\MultiWidthLayoutTrait;
use Drupal\utexas_layouts\Traits\SectionWidthTrait;

/**
 * Configurable two column layout plugin class.
 */
class TwoColumnLayout extends DefaultConfigLayout {

  use BackgroundAccentTrait, BackgroundColorTrait, MultiWidthLayoutTrait, SectionWidthTrait;

  /**
   * {@inheritdoc}
   */
  public function sectionWidthConfiguration() {
    return ['section_width' => 'container'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getWidthOptions() {
    return [
      '50-50' => '50%/50%',
      '33-67' => '33%/67%',
      '67-33' => '67%/33%',
      '25-75' => '25%/75%',
      '75-25' => '75%/25%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config += $this->backgroundConfiguration();
    $config += $this->backgroundColorConfiguration();
    $config += $this->multiWidthConfiguration();
    $config += $this->sectionWidthConfiguration();
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->sectionWidthConfigurationForm($form, $form_state);
    $form = $this->multiWidthConfigurationForm($form, $form_state);
    $form += $this->backgroundConfigurationForm($form, $form_state);
    $form += $this->backgroundColorConfigurationForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->validateMultiWidthConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->submitBackgroundConfigurationForm($form, $form_state);
    $this->submitBackgroundColorConfigurationForm($form, $form_state);
    $this->submitMultiWidthConfigurationForm($form, $form_state);
    $this->submitSectionWidthConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $this->buildBackground($build);
    $this->buildBackgroundColor($build);
    $this->buildMultiWidth($build);
    $this->buildSectionWidth($build);
    return $build;
  }

}
