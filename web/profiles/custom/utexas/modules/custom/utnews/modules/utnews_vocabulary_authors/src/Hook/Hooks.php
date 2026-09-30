<?php

namespace Drupal\utnews_vocabulary_authors\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if ($form_id === 'taxonomy_term_utnews_authors_form') {
      $form['description']['widget'][0]['#title'] = $this->t('Author bio');
      $form['name']['widget'][0]['value']['#title'] = $this->t('Author full name');
    }
  }

}
