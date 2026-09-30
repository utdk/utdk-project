<?php

namespace Drupal\utexas_layouts\Traits;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines layout configuration that includes an option for a background color.
 */
trait BackgroundColorTrait {

  /**
   * {@inheritdoc}
   */
  public function backgroundColorConfiguration() {
    $config['background-color'] = "none";
    return $config;
  }

  /**
   * Reduce legacy color options to fallbacks.
   *
   * @var array colorReductionMap
   */
  public static $colorReductionMap = [
    'f9fafb' => 'e6ebed',
    'c4cdd4' => 'e6ebed',
    '7d8a92' => '5e686e',
    '3e4549' => '5e686e',
    'ebeced' => 'c2c5c8',
    '858c91' => 'c2c5c8',
    '1f262b' => 'c2c5c8',
    'fbfbf9' => 'f2f1ed',
    'e6e4dc' => 'f2f1ed',
    'aba89e' => '807e76',
    '56544e' => '807e76',
  ];

  /**
   * If a site is using a legacy color, replace it with the nearest fallback.
   *
   * @param string $hex
   *   A hex color code.
   *
   * @return string
   *   The fallback color, or if not in the reduction map, the original color.
   */
  public static function getAvailableColor($hex) {
    if (in_array($hex, array_keys(self::$colorReductionMap))) {
      return self::$colorReductionMap[$hex];
    }
    // The hex code is not in the fallback map. Return as-is.
    return $hex;
  }

  /**
   * {@inheritdoc}
   */
  public function backgroundColorConfigurationForm(array $form, FormStateInterface $form_state) {

    $color_palette = [
      'none' => 'Transparent',
      'f2f1ed' => 'Limestone Light <span class="utexas-bg-f2f1ed">&nbsp;&nbsp;&nbsp;</span>',
      'e6ebed' => 'Light shading <span class="utexas-bg-e6ebed">&nbsp;&nbsp;&nbsp;</span>',
      'c2c5c8' => 'Charcoal <span class="utexas-bg-c2c5c8">&nbsp;&nbsp;&nbsp;</span>',
      '807e76' => 'Limestone Dark <span class="utexas-bg-807e76">&nbsp;&nbsp;&nbsp;</span>',
      '5e686e' => 'Dark shading <span class="utexas-bg-5e686e">&nbsp;&nbsp;&nbsp;</span>',
      '487d39' => 'Turtlepond <span class="utexas-bg-487d39">&nbsp;&nbsp;&nbsp;</span>',
      '9d4700' => 'Burnt Orange <span class="utexas-bg-9d4700">&nbsp;&nbsp;&nbsp;</span>',
      '138791' => 'Turquoise <span class="utexas-bg-138791">&nbsp;&nbsp;&nbsp;</span>',
      '074d6a' => 'Bluebonnet <span class="utexas-bg-074d6a">&nbsp;&nbsp;&nbsp;</span>',

    ];
    $form['background-color-wrapper'] = [
      '#type' => 'details',
      '#title' => 'Background color',
    ];
    $form['background-color-wrapper']['background-color'] = [
      '#type' => 'radios',
      '#options' => $color_palette,
      '#default_value' => !empty($this->configuration['background-color']) ? self::getAvailableColor($this->configuration['background-color']) : "none",
      '#name' => 'background_color',
      '#title' => $this->t('Background color'),
      '#weight' => -1,
    ];
    $form['#attached']['library'][] = 'utexas_layouts/background-colors';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitBackgroundColorConfigurationForm(array &$form, FormStateInterface $form_state) {
    $wrapper = $form_state->getValue('background-color-wrapper');
    $this->configuration['background-color'] = $wrapper['background-color'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildBackgroundColor(&$build) {
    if (!empty($this->configuration['background-color'])) {
      $hex_code = self::getAvailableColor($this->configuration['background-color']);
      if ($this->configuration['background-color'] !== "none") {
        $build['#background_color'] = 'utexas-bg-' . $hex_code;
        $build['#attached']['library'][] = 'utexas_layouts/background-colors';
      }
    }
    return $build;
  }

}
