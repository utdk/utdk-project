<?php

namespace Drupal\utprof_content_type_profile\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\utprof_content_type_profile\Form\FormAlter;
use Drupal\utprof_content_type_profile\ProfileContentTypeHelper;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'node__utprof_profile' => [
        'template' => 'node--utprof-profile',
        'base hook' => 'node',
      ],
      'node__utprof_profile__full' => [
        'template' => 'node--utprof-profile--full',
        'base hook' => 'node',
      ],
      'node__utprof_profile__teaser' => [
        'template' => 'node--utprof-profile--teaser',
        'base hook' => 'node',
      ],
      'node__utprof_profile__utexas_basic' => [
        'template' => 'node--utprof-profile--utexas-basic',
        'base hook' => 'node',
      ],
      'node__utprof_profile__utexas_prominent' => [
        'template' => 'node--utprof-profile--utexas-prominent',
        'base hook' => 'node',
      ],
      'node__utprof_profile__utexas_name_only' => [
        'template' => 'node--utprof-profile--utexas-name-only',
        'base hook' => 'node',
      ],
      'field__node__utprof_profile' => [
        'template' => 'field--node--utprof-profile',
        'base hook' => 'field',
      ],
      'field__node__field_utprof_designation' => [
        'template' => 'field--node--field-utprof-designation',
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Implements hook_preprocess_node().
   */
  #[Hook('preprocess_node')]
  public function preprocessNode(&$variables) {
    $node = $variables['elements']['#node'];
    $type = $node->getType();

    if ($type !== 'utprof_profile') {
      return;
    }

    $variables['basic_media'] = ProfileContentTypeHelper::getBasicMedia($node);
    $variables['building_information'] = ProfileContentTypeHelper::getBuildingInformation($node);
    $variables['contact_form_link'] = ProfileContentTypeHelper::getContactLink($node);
    $variables['directory_link'] = ProfileContentTypeHelper::getDirectoryLink($node);
    $variables['has_contact_info'] = ProfileContentTypeHelper::hasContactInfoFields($node);
    $variables['phone_link'] = ProfileContentTypeHelper::getPhone($node);
    $variables['prepared_title'] = ProfileContentTypeHelper::getPreparedTitle($node, 'ut-link');
    $variables['prepared_title_prominent'] = ProfileContentTypeHelper::getPreparedTitle($node, 'ut-link--darker');
    $variables['email_link'] = ProfileContentTypeHelper::getEmail($node);
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if (in_array($form_id, ['node_utprof_profile_edit_form', 'node_utprof_profile_form'])) {
      $alter = \Drupal::classResolver(FormAlter::class);
      $alter->alterProfileNodeForm($form, $form_state, $form_id);
      // Validate email address.
      $form['#validate'][] = [static::class, 'validateEmail'];
    }
  }

  /**
   * Form validation handler for email_address field.
   */
  public static function validateEmail($form, FormStateInterface $form_state) {
    $email = $form_state->getValue('field_utprof_email_address')[0]['value'];
    if ($email !== '' && !\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('field_utprof_email_address', \Drupal::translation()->translate('%email is an invalid email address', ['%email' => $email]));
    }
  }

  /**
   * Helper function to add utprof_profile to workflow config (#361).
   */
  public static function updateWorkflow() {
    $config_id = 'workflows.workflow.standard_workflow';
    $config = \Drupal::service('config.factory')->getEditable($config_id);
    if (is_null($config->get('id'))) {
      \Drupal::logger('utprof_content_type_profile')->notice('Standard workflow not found. Skipping...');
      // This site does not use the standard_workflow. Move on.
      return;
    }
    \Drupal::logger('utprof_content_type_profile')->notice('Standard workflow found. Updating...');
    $nodes = $config->get('type_settings.entity_types.node');
    array_push($nodes, 'utprof_profile');
    $config->set('type_settings.entity_types.node', $nodes);
    $config->save();
    drupal_flush_all_caches();
  }

}
