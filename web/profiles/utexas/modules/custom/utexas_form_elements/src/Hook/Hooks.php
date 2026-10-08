<?php

namespace Drupal\utexas_form_elements\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\utexas_form_elements\DocumentationLinks;
use Drupal\utexas_form_elements\RenderElementHelper;
use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_block_alter().
   */
  #[Hook('block_alter')]
  public function blockAlter(&$definitions) {
    // Alter the "AddToAny" labels (#1867).
    foreach (array_keys($definitions) as $id) {
      if ($id === 'addtoany_block') {
        $definitions[$id]['admin_label'] = 'Share this content';
      }
      if ($id === 'addtoany_follow_block') {
        $definitions[$id]['admin_label'] = 'Follow us';
      }
    }
  }

  /**
   * Implements hook_element_info_alter().
   */
  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$info) {
    \Drupal::classResolver(RenderElementHelper::class)->alterElementInfo($info);
  }

  /**
   * Implements hook_field_widget_info_alter().
   */
  #[Hook('field_widget_info_alter')]
  public function fieldWidgetInfoAlter(array &$definitions) {
    // Alter "Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget".
    $definitions['options_buttons']['class'] = 'Drupal\utexas_form_elements\Plugin\Field\FieldWidget\OptionsButtonsWidget';
    // Alter "Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget".
    $definitions['options_select']['class'] = 'Drupal\utexas_form_elements\Plugin\Field\FieldWidget\OptionsSelectWidget';
    // Alter "Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget".
    $definitions['entity_reference_autocomplete']['class'] = 'Drupal\utexas_form_elements\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget';
    // Alter "Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget".
    $definitions['entity_reference_autocomplete_tags']['class'] = 'Drupal\utexas_form_elements\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget';
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Add documentation to block instances accessed via the Block UI.
    foreach (DocumentationLinks::$docLinks as $block_type => $link) {
      if ($form_id !== 'block_content_' . $block_type . '_form') {
        continue;
      }
      $form['description'] = [
        '#markup' => '<a target="_blank" href="' . $link . '">View documentation for this component<span class="ut-cta-link--external"></span></a>',
        '#weight' => -999,
      ];
      $form['#attached']['library'][] = 'utexas_form_elements/link-options';
    }
  }

  /**
   * Implements hook_form_layout_builder_configure_block_alter().
   */
  #[Hook('form_layout_builder_configure_block_alter')]
  public function formLayoutBuilderConfigureBlockAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Add documentation to block instances accessed via Layout Builder.
    if (!isset($form['settings']['block_form'])) {
      return;
    }
    $bundle = $form['settings']['block_form']['#block']->bundle();
    foreach (DocumentationLinks::$docLinks as $block_type => $link) {
      if ($bundle !== $block_type) {
        continue;
      }
      $label = $form['settings']['admin_label']['#plain_text'];
      unset($form['settings']['admin_label']['#plain_text']);
      $form['settings']['admin_label']['#description'] = [
        '#markup' => $label . ' (<a target="_blank" href="' . $link . '">View documentation for this component<span class="ut-cta-link--external"></span></a>)',
        '#weight' => -999,
      ];
      $form['#attached']['library'][] = 'utexas_form_elements/link-options';
    }
  }

  /**
   * Implements hook_form_node_form_alter().
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    $form['actions']['delete']['#title'] = $this->t('Move to trash');
    // Add an "Archive" submit handler if the node uses the "Standard Workflow"
    // and the current user has the workflow transition permission.
    /** @var \Drupal\content_moderation\ModerationInformationInterface $moderation_info */
    $moderation_info = \Drupal::service('content_moderation.moderation_information');
    /** @var \Drupal\Core\Entity\ContentEntityForm $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\Entity\Node $node */
    $node = $form_object->getEntity();
    $op = $form_object->getOperation();
    if ($op !== 'edit') {
      return;
    }
    if (!$moderation_info->isModeratedEntity($node)) {
      return;
    }
    if ($moderation_info->getWorkflowForEntity($node)->id() !== 'standard_workflow') {
      return;
    }
    if (!\Drupal::currentUser()->hasPermission('use standard_workflow transition archive')) {
      return;
    }
    $delete_weight = $form['actions']['delete']['#weight'];
    $form['actions']['archive'] = [
      '#type' => 'submit',
      '#value' => $this->t('Archive'),
      '#weight' => $delete_weight - 1,
      '#attributes' => [
        'class' => ['button', 'button--danger'],
      ],
    ];
    $form['actions']['archive']['#submit'] = $form['actions']['submit']['#submit'];
    array_unshift($form['actions']['archive']['#submit'], [$this, 'archiveSubmit']);
  }

  /**
   * Submit callback to set a node using "Standard workflow" to "archived".
   */
  public function archiveSubmit(&$form, FormStateInterface $form_state) {
    $form_state->setValue(['moderation_state', '0', 'value'], 'archived');
    $messenger = \Drupal::service("messenger");
    $messenger->addMessage($this->t('This page has been archived. It will not be visible to anonymous visitors. It can be made visible again by setting its state to "Published".'));
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_menu_link_content_form_alter')]
  public function formMenuLinkContentAlter(array &$form, FormStateInterface $form_state, $form_id) {
    // Modify Drupal core menu link interface to include UTexas link options.
    $form['#entity_builders']['utexas_form_elements'] = [$this, 'menuLinkContentFormEntityBuilder'];
    $menu_link = $form_state->getFormObject()->getEntity();
    $form['#default_value']['options'] = [];
    $link = $menu_link->link->getValue();
    if (isset($link[0]['options'])) {
      $form['#default_value']['options'] = $link[0]['options'];
    }
    if ($menu_link->isDefaultTranslationAffectedOnly() && !$menu_link->isDefaultTranslation()) {
      return;
    }
    // Add link options form element.
    $link_options_helper = new UtexasLinkOptionsHelper();
    $form = $link_options_helper->addLinkOptions($form);
  }

  /**
   * Implements hook_form_FORMTPYE_alter().
   */
  #[Hook('form_search_block_form_alter')]
  public function formSearchBlockFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    $form['keys']['#attributes']['placeholder'] = 'Search the site...';
    unset($form['keys']['#attributes']['title']);
    $form['actions']['#attributes']['id'] = 'edit-search-actions';
  }

  /**
   * Implements hook_form_FORMTPYE_alter().
   */
  #[Hook('form_user_login_form_alter')]
  public function formUserLoginFormAlter(&$form, &$form_state, $form_id) {
    $form['actions']['submit']['#attributes']['class'][] = 'ut-btn';
  }

  /**
   * Implements hook_library_info_alter().
   */
  #[Hook('library_info_alter')]
  public function libraryInfoAlter(&$libraries, $extension) {
    if ($extension === 'ckeditor5') {
      $theme = \Drupal::configFactory()->getEditable('system.theme')->get('default');
      $themeinfo = \Drupal::service('extension.list.theme')->getExtensionInfo($theme);
      $basetheme = $themeinfo['base theme'] ?? '';
      $eligible_themes = ['speedway'];
      $is_eligible_theme = in_array($theme, $eligible_themes) || in_array($basetheme, $eligible_themes);
      // Add paths to stylesheets specified by a modules's ckeditor5-stylesheets
      // config property.
      $module = 'utexas_form_elements';
      $module_path = \Drupal::service('extension.list.module')->getPath($module);
      $info = \Drupal::service('extension.list.module')->getExtensionInfo($module);
      if (isset($info['ckeditor5-stylesheets']) && $info['ckeditor5-stylesheets'] !== FALSE) {
        $css = $info['ckeditor5-stylesheets'];
        foreach (array_values($css) as $url) {
          $path = '/' . $module_path . '/' . $url;
          // Only load utexas_form_elements ckeditor5-stylesheets if the active
          // theme or base theme is eligible.
          if (!$is_eligible_theme) {
            continue;
          }
          $libraries['internal.drupal.ckeditor5.stylesheets']['css']['theme'][$path] = [];
        }
      }
    }
  }

  /**
   * Implements hook_contextual_links_alter().
   */
  #[Hook('local_tasks_alter')]
  public function localTasksAlter(&$local_tasks) {
    // Do not display the 'Delete' tab on node forms
    // Nodes can still be deleted from within the 'Edit' interface.
    unset($local_tasks['entity.node.delete_form']);
  }

  /**
   * Implements entity_builder API (#2856660).
   */
  public function menuLinkContentFormEntityBuilder($entity_type, $menu_link, &$form, &$form_state) {
    if (!$menu_link->link || $menu_link->link->isEmpty()) {
      return;
    }

    if ($menu_link->isDefaultTranslation() || !$menu_link->isDefaultTranslationAffectedOnly()) {
      $target = $form_state->getValue('target');
      $class = $form_state->getValue('class');
      $menu_link_options = [
        'attributes' => [],
      ];
      if (!empty($target)) {
        $menu_link_options['attributes']['target'] = $target;
      }
      if (!empty($class)) {
        $menu_link_options['attributes']['class'] = $class;
      }
    }
    else {
      $original = \Drupal::entityTypeManager()->getStorage($menu_link->getEntityTypeId());
      /** @var \Drupal\Core\Entity\RevisionableStorageInterface $original */
      $original->loadRevision($menu_link->getLoadedRevisionId());
      $menu_link_options = $original->get('link')->first()->options;
    }
    $menu_link->link->first()->options = $menu_link_options;
    // Multilingual: set for other languages.
    if ($menu_link->isDefaultTranslation() || !$menu_link->isDefaultTranslationAffectedOnly()) {
      foreach ($menu_link->getTranslationLanguages() as $language) {
        if ($language->getId() != $menu_link->language()->getId()) {
          $menu_link->getTranslation($language->getId())->link->first()->options = $menu_link_options;
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_menu().
   */
  #[Hook('preprocess_menu')]
  public function preprocessMenu(&$variables) {
    // Call recursive function to handle nested menu links.
    self::preprocessMenuItems($variables['items']);
  }

  /**
   * Helper function to recursively set link item attributes.
   */
  public static function preprocessMenuItems(&$items) {
    foreach ($items as $item) {
      /** @var \Drupal\Core\Menu\MenuLinkInterface $menu_link */
      $menu_link = $item['original_link'] ?? NULL;
      if (!empty($menu_link)) {
        $options = ($menu_link instanceof MenuLinkContentInterface) ?
          $menu_link->link->first()->options : $menu_link->getOptions();
        // Put attributes on link element.
        if (isset($options) && isset($options['attributes'])) {
          foreach ($options['attributes'] as $attribute => $value) {
            $item['attributes']->setAttribute($attribute, $value);
          }
        }
      }
      if (!empty($item['below'])) {
        self::preprocessMenuItems($item['below']);
      }
    }
  }

  /**
   * Implements hook_preprocess_HOOK() for table.
   */
  #[Hook('preprocess_table')]
  public function preprocessTable(&$variables): void {
    // Add a CSS target to draggable tables with <td> elements with <details>.
    if (!empty($variables['rows'])) {
      foreach ($variables['rows'] as &$row) {
        /** @var \Drupal\Core\Template\Attribute $row['attributes'] */
        if (!empty($row['attributes']) && $row['attributes']->hasClass('utexas-draggable')) {
          // The 'attributes' key is always here and it is an
          // \Drupal\Core\Template\Attribute.
          // @see \Drupal\Core\Theme\ThemePreprocess::preprocessTable();
          $row['cells'][1]['attributes']->addClass('utexas-tabledrag-cell');
        }
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    $variables = [
      'utexas_link_options_element' => [
        'render element' => 'element',
        'template' => 'utexas-link-options-element',
      ],
      'utexas_link_options_element_multiple' => [
        'render element' => 'element',
        'template' => 'utexas-link-options-element-multiple',
      ],
    ];
    return $variables;
  }

  /**
   * Implements hook_views_data().
   */
  #[Hook('views_data')]
  public function viewsData() {
    $data['views']['add_flex_page'] = [
      'title' => $this->t('Link for "Add new Flex Page content"'),
      'help' => $this->t('Display an "Add new Flex Page content" link.'),
      'area' => [
        'id' => 'add_flex_page',
      ],
    ];
    $data['views']['set_site_homepage'] = [
      'title' => $this->t('Link for "Set site homepage"'),
      'help' => $this->t('Display a "Set site homepage" link.'),
      'area' => [
        'id' => 'set_site_homepage',
      ],
    ];
    return $data;
  }

}
