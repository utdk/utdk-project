<?php

namespace Drupal\utexas_hero\Hook;

use Drupal\block\BlockForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    $variables = [
      'utexas_hero' => [
        'variables' => [
          'media' => NULL,
          'heading' => NULL,
          'subheading' => NULL,
          'caption' => NULL,
          'credit' => NULL,
          'cta' => NULL,
        ],
        'template' => 'utexas-hero',
      ],
      'utexas_hero_1' => [
        'variables' => [
          'media_identifier' => NULL,
          'heading' => NULL,
          'subheading' => NULL,
          'caption' => NULL,
          'credit' => NULL,
          'cta' => NULL,
          'alt' => NULL,
          'anchor_position' => NULL,
        ],
        'template' => 'utexas-hero-1',
      ],
      'utexas_hero_2' => [
        'variables' => [
          'media_identifier' => NULL,
          'heading' => NULL,
          'subheading' => NULL,
          'caption' => NULL,
          'credit' => NULL,
          'cta' => NULL,
          'alt' => NULL,
          'anchor_position' => NULL,
        ],
        'template' => 'utexas-hero-2',
      ],
      'utexas_hero_3' => [
        'variables' => [
          'media_identifier' => NULL,
          'heading' => NULL,
          'subheading' => NULL,
          'caption' => NULL,
          'credit' => NULL,
          'cta' => NULL,
          'alt' => NULL,
          'anchor_position' => NULL,
        ],
        'template' => 'utexas-hero-3',
      ],
      'utexas_hero_4' => [
        'variables' => [
          'media' => NULL,
          'heading' => NULL,
          'subheading' => NULL,
          'caption' => NULL,
          'credit' => NULL,
          'cta' => NULL,
        ],
        'template' => 'utexas-hero-4',
      ],
      'utexas_hero_5' => [
        'variables' => [
          'media_identifier' => NULL,
          'heading' => NULL,
          'subheading' => NULL,
          'caption' => NULL,
          'credit' => NULL,
          'cta' => NULL,
          'alt' => NULL,
          'anchor_position' => NULL,
        ],
        'template' => 'utexas-hero-5',
      ],
    ];
    return $variables;
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * Add logic to the place block form.
   */
  #[Hook('form_block_form_alter')]
  public function blockFormAlter(&$form, &$form_state, $form_id) {
    $bundle = FALSE;
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof BlockForm) {
      /** @var \Drupal\block\Entity\Block $entity */
      $entity = $form_object->getEntity();
      $uuid = $entity->getPlugin()->getDerivativeId();
      /** @var Drupal\block_content\Entity\BlockContent $block_content */
      if (isset($uuid)) {
        $block_content = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $uuid) ?? "";
        if (method_exists($block_content, 'bundle')) {
          $bundle = $block_content->bundle();
          if ($bundle == 'utexas_hero') {
            $form['settings']['view_mode']['#options'] = $this->updateViewModeLabels($form['settings']['view_mode']['#options']);
            $this->splitEntityTypeValidation($form);
          }
        }
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_layout_builder_update_block_alter')]
  public function updateBlockAlter(&$form, FormStateInterface $form_state, $form_id) {
    $this->simplifyLayoutForm($form);
    $this->splitEntityTypeValidation($form);
  }

  /**
  * Implements hook_form_FORM_ID_alter().
  */
  #[Hook('form_layout_builder_add_block_alter')]
  public function addBlockAlter(&$form, FormStateInterface $form_state, $form_id) {
    $this->simplifyLayoutForm($form);
    $this->splitEntityTypeValidation($form);
  }

  /**
   * Helper function for Layout Builder form alters.
   */
  protected function simplifyLayoutForm(&$form) {
    $is_hero = FALSE;
    // Check inline block view mode labels.
    if (isset($form['settings']['block_form'])) {
      $bundle = $form['settings']['block_form']['#block']->bundle();
      if ($bundle === 'utexas_hero') {
        $is_hero = TRUE;
      }
    }
    // Check reusable block view mode labels.
    if (isset($form['settings']['provider'])) {
      if ($form['settings']['provider']['#value'] == 'block_content') {
        $options = array_keys($form['settings']['view_mode']['#options']);
        if (in_array('utexas_hero_2', $options)) {
          $is_hero = TRUE;
        }
      }
    }
    if ($is_hero) {
      $form['settings']['view_mode']['#options'] = $this->updateViewModeLabels($form['settings']['view_mode']['#options']);
    }
  }

  /**
   * Helper function for updating view mode labels.
   */
  protected function updateViewModeLabels($options) {
    $formatterManager = \Drupal::service('plugin.manager.field.formatter');
    $definitions = $formatterManager->getDefinitions();
    $map = $this->mapFormatterLabels($definitions, 'utexas_hero');
    // Use keys from the available $options to get the matching key-value pairs.
    return array_intersect_key($map, $options);
  }

  /**
   * Helper function for generating mapped array of the widget formatter labels.
   */
  protected function mapFormatterLabels($definitions, $widget_type) {
    $map = [];
    foreach ($definitions as $key => $value) {
      if (strpos($key, $widget_type) !== FALSE) {
        $map[$key] = $definitions[$key]['label']->__toString();
      }
    }
    // Convert first key to "default" to be used by view modes.
    $map['default'] = $map[$widget_type];
    unset($map[$widget_type]);
    // Sort the array by machine name.
    asort($map);
    return $map;
  }

  /**
   * Helper function for Layout Builder to validate if hero block or node type.
   */
  protected function splitEntityTypeValidation(&$form) {
    // Checking if form contains an inline hero block.
    if (isset($form['settings']['block_form'])) {
      $bundle = $form['settings']['block_form']['#block']->bundle();
      if ($bundle === 'utexas_hero') {
        $form = $this->addImageFormElements($form, 'block');
      }
    }
    // Checking if form contains a reusable hero block.
    if (isset($form['settings']['view_mode'])) {
      $selector = $form['settings']['view_mode']['#options'];
      $option_keys = array_keys($selector);
      foreach ($option_keys as $option) {
        if (strpos($option, 'utexas_hero') !== FALSE) {
          $form = $this->addImageFormElements($form, 'block');
          break;
        }
      }
    }
    // Checking if node form is of type hero.
    if (isset($form['settings']['formatter'])) {
      $formatter = $form['settings']['formatter']['type']['#default_value'];
      if (strpos($formatter, 'utexas_hero') !== FALSE) {
        $form = $this->addImageFormElements($form, 'node');
      }
    }
  }

  /**
   * Helper function that creates the necessary form elements to pick a style.
   */
  protected function addImageFormElements($form, $entity_type) {
    $form['#attached']['library'][] = 'utexas_hero/hero_formatters_split';
    $options = $form['settings']['view_mode']['#options'];
    $suppress = [
      'utexas_hero_1_left',
      'utexas_hero_1_right',
      'utexas_hero_2_left',
      'utexas_hero_2_right',
      'utexas_hero_3_left',
      'utexas_hero_3_right',
      'utexas_hero_5_left',
      'utexas_hero_5_right',
    ];
    foreach ($suppress as $s) {
      unset($options[$s]);
    }
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $displayed_styles = [
      'default' => '<span>Default style:</span> <a href="https://demo.drupalkit.its.utexas.edu/hero-styles/default" target="_blank">Default: Large media with optional caption and credit<span class="ut-cta-link--external"></span></a>',
      'utexas_hero_1' => '<span>Style 1:</span> <a href="https://demo.drupalkit.its.utexas.edu/hero-styles/1" target="_blank">Bold heading & subheading on burnt orange background<span class="ut-cta-link--external"></span></a>',
      'utexas_hero_2' => '<span>Style 2:</span> <a href="https://demo.drupalkit.its.utexas.edu/hero-styles/2" target="_blank">Bold heading on dark background, anchored at base of media<span class="ut-cta-link--external"></span></a>',
      'utexas_hero_3' => '<span>Style 3:</span> <a href="https://demo.drupalkit.its.utexas.edu/hero-styles/3" target="_blank">White bottom pane with heading, subheading and burnt orange call to action<span class="ut-cta-link--external"></span></a>',
      'utexas_hero_4' => '<span>Style 4:</span> <a href="https://demo.drupalkit.its.utexas.edu/hero-styles/4" target="_blank">Centered image with dark bottom pane containing heading, subheading and call-to-action<span class="ut-cta-link--external"></span></a>',
      'utexas_hero_5' => '<span>Style 5:</span> <a href="https://demo.drupalkit.its.utexas.edu/hero-styles/5" target="_blank">Medium image, floated right, with large heading, subheading and burnt orange call-to-action<span class="ut-cta-link--external"></span></a>',
    ];
    // Add any view modes not specified above.
    foreach ($options as $machine_name => $label) {
      if (!in_array($machine_name, array_keys($displayed_styles))) {
        $displayed_styles[$machine_name] = $label;
      }
    }
    $hero_positions = [
      'center' => '<span>Center</span>',
      'left' => '<span>Left</span>',
      'right' => '<span>Right</span>',
    ];
    $form['utexas_hero_style_selector'] = [
      '#type' => 'radios',
      '#default_value' => "default",
      '#title' => $this->t('Hero style'),
      '#options' => $displayed_styles,
    ];
    $form['utexas_hero_anchor'] = [
      '#type' => 'radios',
      '#default_value' => "center",
      '#title' => $this->t('Image anchor position'),
      '#description' => $this->t('Set what part of the image should be the focal anchor. This is only applicable to Styles 1, 2, 3, and 5.'),
      '#description_display' => 'before',
      '#options' => $hero_positions,
    ];
    return $form;
  }

}
