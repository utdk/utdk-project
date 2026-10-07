<?php

namespace Drupal\utexas_site_announcement\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\block\Entity\Block;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure settings for the site announcement.
 */
class AnnouncementConfigurationForm extends ConfigFormBase {

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   The typed config manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager, TypedConfigManagerInterface $typed_config_manager) {
    parent::__construct($config_factory, $typed_config_manager);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('config.typed'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utexas_site_announcement_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Dependency injection is more complicated code than static calls
    // and therefore has a negative Developer Experience (DX) for our team.
    // We mark these PHPCS standards as ignored.
    // phpcs:ignore
    if ($block = Block::load('siteannouncement')) {
      // Initially populate block settings as previously defined by this form.
      $settings = $block->get('settings');
      // The block status value always takes precedence over the setting.
      if (!$block->status()) {
        $settings['state'] = 'inactive';
      }
      else {
        // The block is active, so determine "homepage" or "all" state.
        // This is subject to change when per-page visibility is supported.
        $visibility = $block->getVisibility();
        if (isset($visibility['request_path']['pages'])) {
          if ($visibility['request_path']['pages'] === "<front>") {
            $settings['state'] = 'homepage';
          }
        }
        else {
          $settings['state'] = 'all';
        }
      }
    }
    $form['state'] = [
      '#type' => 'radios',
      '#title' => $this->t("Announcement status"),
      '#options' => [
        'inactive' => $this->t("Inactive"),
        'homepage' => $this->t("Active on homepage only"),
        'all' => $this->t("Active on all pages"),
      ],
      '#default_value' => $settings['state'] ?? 'inactive',
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Announcement title'),
      '#required' => TRUE,
      '#description' => $this->t('Enter the text that should appear as the headline for the announcement'),
      '#default_value' => $settings['title'] ?? '',
    ];
    $icons = $this->entityTypeManager->getStorage('utexas_announcement_icon')->loadMultiple();
    foreach ($icons as $icon) {
      $icon_content = file_get_contents($icon->get('icon'));
      $icon_options[$icon->id()] = $this->t('%icon :txt',
       [
         '%icon' => Markup::create($icon_content),
         ':txt' => $icon->get('label'),
       ]);
    }
    // Account for no available icons.
    if (empty($icon_options)) {
      $icon_options = ['none' => $this->t('None')];
    }
    $icon_option_keys = array_keys($icon_options);
    $default_icon_option = reset($icon_option_keys);
    $form['icon'] = [
      '#type' => 'radios',
      '#title' => $this->t("Icon to display"),
      '#options' => $icon_options,
      '#default_value' => $settings['icon'] ?? $default_icon_option,
    ];
    $schemes = $this->entityTypeManager->getStorage('utexas_announcement_color_scheme')->loadMultiple();
    foreach ($schemes as $scheme) {
      $scheme_options[$scheme->id()] = $this->t('<span style="background-color: :bg">&nbsp;&nbsp;&nbsp;&nbsp;</span> (:bg) background with <span style="background-color: :txt; border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> (:txt) text.',
      [
        ':bg' => $scheme->get('background_color'),
        ':txt' => $scheme->get('text_color'),
      ]);
    }
    // Account for no available color schemes.
    if (empty($scheme_options)) {
      $scheme_options = ['none' => $this->t('None')];
    }
    $scheme_option_keys = array_keys($scheme_options);
    $default_scheme = reset($scheme_option_keys);
    $form['scheme'] = [
      '#type' => 'radios',
      '#title' => $this->t("Color scheme"),
      '#options' => $scheme_options,
      '#default_value' => $settings['scheme'] ?? $default_scheme,
    ];
    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Announcement body'),

      '#default_value' => $settings['message']['value'] ?? '',
    ];
    $form['message'] = [
      '#title' => 'Message',
      '#type' => 'text_format',
      '#default_value' => $settings['message']['value'] ?? '',
      '#description' => $this->t('A brief message that serves as the announcement text.'),
      '#format' => $settings['message']['format'] ?? 'restricted_html',
    ];
    $form['cta_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t("Call to Action"),
    ];
    $form['cta_wrapper']['cta'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $settings['cta']['uri'] ?? '',
        'title' => $settings['cta']['title'] ?? '',
        'options' => $settings['cta']['options'] ?? '',
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = [
      'label' => 'Site Announcement',
    ];
    $form_elements = [
      'state',
      'title',
      'message',
      'cta',
      'icon',
      'scheme',
    ];
    foreach ($form_elements as $element) {
      $config[$element] = $form_state->getValue($element);
    }
    // Turn off the block label display.
    $config['label_display'] = "0";
    // Dependency injection is more complicated code than static calls
    // and therefore has a negative Developer Experience (DX) for our team.
    // We mark these PHPCS standards as ignored.
    // phpcs:ignore
    if ($block = Block::load('siteannouncement')) {
      $block->set('settings', $config);
    }
    else {
      // Instantiate the block for an edge case where it has been deleted.
      $blockEntityManager = $this->entityTypeManager->getStorage('block');
      $block = $blockEntityManager->create([
        'id' => 'siteannouncement',
        'settings' => $config,
        'plugin' => 'utexas_announcement',
        'theme' => $this->config('system.theme')->get('default'),
      ]);
      // @todo how to make this developer-configurable?
      $block->setRegion('site_announcement');
    }
    // Set the block enabled/disabled status per form selection.
    if ($config['state'] === "inactive") {
      $block->disable();
    }
    else {
      $block->enable();
      // Ensure that this is set in the currently active theme (edge case)
      // where the theme is changed after creating an announcement.
      $block->set('theme', $this->config('system.theme')->get('default'));
      $visibility = $block->getVisibility();
      // Set the block visibility per "all" or "homepage".
      // Note: this is subject to change if per-path configuration
      // becomes supported.
      if ($config['state'] === "homepage") {
        $visibility['request_path']['pages'] = "<front>";
      }
      else {
        // Default to all pages.
        $visibility['request_path'] = [];
      }
      $block->setVisibilityConfig("request_path", $visibility['request_path']);
    }
    $block->save();

    parent::submitForm($form, $form_state);
  }

}
