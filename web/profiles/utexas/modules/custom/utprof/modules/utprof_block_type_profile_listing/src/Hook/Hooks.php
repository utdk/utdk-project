<?php

namespace Drupal\utprof_block_type_profile_listing\Hook;

use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utprof_block_type_profile_listing\Form\FormAlter;
use Drupal\utprof_block_type_profile_listing\Plugin\EntityReferenceSelection\ViewModeSelection;
use Drupal\utprof_block_type_profile_listing\ProfileListingHelper;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_block_alter')]
  public function themeSuggestionsBlockAlter(array &$suggestions, array $variables) {
    // Add a template suggestion for the utprof_profile_listing bundle.
    if (isset($variables['elements']['content']['#block_content'])) {
      $theme_hook_original = $variables['theme_hook_original'];
      $base_plugin_id = $variables['elements']['#base_plugin_id'];
      $bundle = $variables['elements']['content']['#block_content']->bundle();
      // Theme suggestions for custom inline blocks are already correctly added
      // by core, so we do not want to add another one here.
      if ($bundle === 'utprof_profile_listing' && $base_plugin_id != 'inline_block') {
        // Add a bundle-specific theme suggestion.
        array_splice($suggestions, 1, 0, $theme_hook_original . '__' . $base_plugin_id . '__' . $bundle);
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    // Register the templates defined in /templates.
    return [
      'block__block_content__utprof_profile_listing' => [
        'base hook' => 'block',
      ],
      'block__inline_block__utprof_profile_listing' => [
        'base hook' => 'block',
      ],
    ];
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    // Add a rendered View of the Profile listings matching criteria specified
    // from the block's fields.
    $content = $variables['elements']['content'];
    if (isset($content['#block_content']) && $content['#block_content'] instanceof BlockContentInterface) {
      $block = $content['#block_content'];
      if ($block->bundle() == 'utprof_profile_listing') {
        $method = $block->get('field_utprof_list_method');
        if ($method->value === 'pick') {
          $listing = ProfileListingHelper::buildList($block);
        }
        else {
          // Default to a filter-based display listing.
          $listing = ProfileListingHelper::buildContextualView($block);
        }
        if ($listing) {
          $variables['listing'] = $listing;
          $variables['attributes']['class'][] = ProfileListingHelper::getViewMode($block) . '__wrapper';
        }
      }
    }
  }

  /**
   * Sets the default value for field_utprof_view_mode.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity being created.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $definition
   *   The field definition.
   *
   * @return array
   *   Array of default value keys with each entry keyed with the "value" key.
   *
   * @see \Drupal\Core\Field\FieldConfigBase::getDefaultValue()
   */
  public static function viewModeDefaultValue(ContentEntityInterface $entity, FieldDefinitionInterface $definition) {
    // Use the first allowed value as the default value.
    $allowed_values = array_keys(ViewModeSelection::getAllowedViewModes());
    return reset($allowed_values);
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, string $form_id) {
    if (in_array($form_id, [
      'block_content_utprof_profile_listing_form',
      'block_content_utprof_profile_listing_edit_form',
    ])) {
      $alter = \Drupal::classResolver(FormAlter::class);
      $alter->alterProfileListingForm($form, $form_state, $form_id);
    }

    // Replicate form alter behavior in the context of inline blocks via process
    // callback. This solves the problem described in
    // https://www.drupal.org/project/drupal/issues/3028391 .
    if ($form_id == 'layout_builder_add_block' || $form_id == 'layout_builder_update_block') {
      if ($form_id == 'layout_builder_add_block') {
        $storage = $form_state->getStorage();
        $component_config = $storage['layout_builder__component']->get('configuration');
      }
      elseif ($form_id == 'layout_builder_update_block') {
        $build_info = $form_state->getBuildInfo();
        $block = $build_info['callback_object']->getCurrentComponent();
        $component_config = $block->get('configuration');
      }
      $block_id = explode(':', $component_config['id']);
      $plugin_id = $block_id[0];

      // Inline blocks.
      if ($plugin_id == 'inline_block') {
        // Because the block form is added with a process function for
        // inline blocks, it is necessary to alter them via a subsequent process
        // function.
        $form['settings']['block_form']['#process'][] = [static::class, 'inlineBlockProcess'];
      }
    }
  }

  /**
   * Process callback for inline block forms.
   *
   * @param array $element
   *   The containing element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The containing element, as altered.
   */
  public static function inlineBlockProcess(array &$element, FormStateInterface &$form_state) {
    $block_type = $element['#block']->bundle();
    if ($block_type === 'utprof_profile_listing') {
      $alter = \Drupal::classResolver(FormAlter::class);
      $alter->alterProfileListingForm($element, $form_state);
    }
    return $element;
  }

}
