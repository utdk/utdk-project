<?php

namespace Drupal\utexas_content_type_flex_page\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\node\NodeInterface;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(&$form, &$form_state, $form_id) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $form_state->getFormObject()->getEntity();
    if ($node->getType() !== 'utexas_flex_page') {
      return;
    }
    $message = '';
    switch ($form_id) {
      case 'node_utexas_flex_page_edit_form':
        $message = $this->t('<ul><li>To set page metadata and control the display of the page title & breadcrumbs, use the vertical tabs on this page.</li><li>To add content, click the "Layout" tab, above.</li></ul>');
        break;

      case 'node_utexas_flex_page_form':
        $message = $this->t('To add content to this page, first add a title and save this form. A "Layout" tab will then appear, from which you can add content.');
        break;
    }
    if ($message !== '') {
      $form['utexas_content_type_flex_page_help_text'] = [
        '#markup' => '<div class="messages messages--status">' . $message . '</div>',
        '#weight' => -999,
      ];
    }
  }

  /**
   * Implements hook_plugin_filter_TYPE__CONSUMER_alter().
   */
  #[Hook('plugin_filter_layout__layout_builder_alter')]
  public function flexPageLayoutBuilderLayoutFilter(array &$definitions, array $extra) {
    $allowed_layouts = [
      'layout_utexas_onecol',
      'layout_utexas_twocol',
      'layout_utexas_threecol',
      'layout_utexas_fourcol',
    ];
    if (self::isFlexPageStorage($extra)) {
      foreach ($definitions as $plugin_id => $definition) {
        if (!in_array($plugin_id, $allowed_layouts)) {
          unset($definitions[$plugin_id]);
        }
      }
    }
  }

  /**
   * Implements hook_plugin_filter_TYPE__CONSUMER_alter().
   */
  #[Hook('plugin_filter_block__layout_builder_alter')]
  public static function flexPageLayoutBuilderBlockFilter(array &$definitions, array $extra) {
    $disallowed_categories = [
      'Chaos Tools',
      'Content fields',
      'Forms',
      'Last Updated',
      'System',
      'User',
      'core',
    ];

    if (self::isFlexPageStorage($extra)) {
      foreach ($definitions as $plugin_id => $definition) {
        $category = self::getUntranslatedCategory($definition['category']);
        if (in_array($category, $disallowed_categories)) {
          unset($definitions[$plugin_id]);
        }
      }
    }
  }

  /**
   * Checks whether we are on a Flex Page node.
   *
   * @param mixed[] $extra
   *   The data sent to Layout Builder.
   *
   * @return bool
   *   Whether or not this is a Flex Page.
   */
  public static function isFlexPageStorage($extra) {
    if (isset($extra['section_storage']) && $extra['section_storage'] instanceof SectionStorageInterface) {
      $section_storage = $extra['section_storage'];
      // Extract the entity from the storage.
      $entity = $section_storage->getContextValue('entity');
      // Ensure it is a Node of type `utexas_flex_page`.
      if ($entity instanceof NodeInterface && $entity->bundle() === 'utexas_flex_page') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Helper function to return an untranslated block Category.
   *
   * @param mixed $category
   *   The block category name or object.
   *
   * @return string
   *   A string representing the untranslated block category.
   */
  public static function getUntranslatedCategory($category) {
    if ($category instanceof TranslatableMarkup) {
      $output = $category->getUntranslatedString();
      // Rename to match Layout Builder Restrictions naming.
      if ($output == '@entity fields') {
        $output = 'Content fields';
      }
      if ($output === "Custom") {
        $output = "Custom blocks";
      }
      // Add affordance for name change in Drupal 10.1. "Custom blocks" are now
      // "Content block". We use the legacy name for compatibility reasons.
      // See #3363076.
      if ($output === "Content block") {
        $output = "Custom blocks";
      }
    }
    else {
      $output = (string) $category;
    }

    return $output;
  }

}
