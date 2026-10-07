<?php

namespace Drupal\utexas_form_elements\Traits;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * General-purpose method for modifying taxonomy widget descriptions.
 */
trait TaxonomyDescriptionTrait {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, AccountProxy $current_user) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('current_user')
    );
  }

  /**
   * Main function to manipulate taxonomy description text.
   *
   * @param array $element
   *   The form element array.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field defintion.
   *
   * @return array
   *   The modified $element.
   */
  protected function addDynamicTaxonomyDescription(array $element, FieldItemListInterface $items) {
    $prepared_description = [];
    $prepared_description[] = $element['#description'];
    if (!$element['#required']) {
      array_unshift($prepared_description, $this->t('Optional.'));
    }
    // Determine whether there are available options.
    $has_options = FALSE;
    $target_bundles = $this->getSelectionHandlerSetting('target_bundles');
    if (!empty($target_bundles)) {
      foreach ($target_bundles as $bundle) {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
          'vid' => $bundle,
        ]);
        if (count($terms) !== 0) {
          $has_options = TRUE;
          break;
        }
      }
    }
    if (!$has_options) {
      $prepared_description[] = $this->t('<strong>No terms currently exist.</strong>');
    }
    $term_text = $this->t('manage taxonomy terms');
    $url = Url::fromRoute('entity.taxonomy_vocabulary.collection', []);
    $term_text = Link::fromTextAndUrl($term_text, $url)->toString();
    $prepared_description[] = $this->t('Users with the appropriate permissions can @manage.', ['@manage' => $term_text]);
    $element['#description'] = implode(' ', $prepared_description);
    return $element;
  }

  /**
   * Returns the value of a setting for the entity reference selection handler.
   *
   * @param string $setting_name
   *   The setting name.
   *
   * @return mixed
   *   The setting value.
   */
  protected function getSelectionHandlerSetting($setting_name) {
    $settings = $this->getFieldSetting('handler_settings');
    return $settings[$setting_name] ?? NULL;
  }

}
