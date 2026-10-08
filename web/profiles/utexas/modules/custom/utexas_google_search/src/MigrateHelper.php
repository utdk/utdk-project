<?php

namespace Drupal\utexas_google_search;

use Drupal\search\Entity\SearchPage;

/**
 * Provides helpers for Google CSE migration updates.
 */
class MigrateHelper {

  /**
   * Migrates Google CSE data to UTexas Google Search.
   */
  public static function migrateFromGoogleCse(): void {
    if (\Drupal::moduleHandler()->moduleExists('search') === FALSE) {
      return;
    }
    $google_cse_search_pages = [];
    $google_cse_is_default = FALSE;
    $search_implementations = SearchPage::loadMultiple();
    foreach ($search_implementations as $search) {
      // Delete all Google CSE configurations (admin/config/search/pages).
      if ($search->getPlugin()->getPluginId() == 'google_cse_search') {
        $google_cse_search_pages[] = $search->id();
        if ($search->isDefaultSearch()) {
          $google_cse_is_default = TRUE;
        }
        $config = \Drupal::service('config.factory')->getEditable('search.page.' . $search->id());
        $configuration = $search->getPlugin()->getConfiguration();
        \Drupal::logger('utexas_google_search')->notice('Migrating Google PSE ID %id', [
          '%id' => $configuration['cx'],
        ]);
        \Drupal::state()->set('utexas.google_pse_id', $configuration['cx']);
        $config->delete();
      }
    }
    // Load all search form blocks.
    $search_form_blocks = \Drupal::service('entity_type.manager')
      ->getStorage('block')
      ->loadByProperties([
        'plugin' => 'search_form_block',
      ]);
    foreach ($search_form_blocks as $block) {
      $settings = $block->get('settings');

      if ($block->status() === FALSE) {
        $block->delete();
      }
      $this_is_google_cse = FALSE;
      // Check for blocks that explicitly set the search to Google CSE
      // or sites where the default search is Google CSE.
      if ($settings['provider'] === 'google_cse') {
        $this_is_google_cse = TRUE;
      }
      if (is_null($settings['page_id']) && $google_cse_is_default) {
        $this_is_google_cse = TRUE;
      }
      if (in_array($settings['page_id'], $google_cse_search_pages)) {
        $this_is_google_cse = TRUE;
      }
      if ($this_is_google_cse) {
        $block_id = $block->id();
        $theme = $block->getTheme();
        $region = $block->getRegion();
        $weight = $block->getWeight();
        $visibility = $block->getVisibility();
        \Drupal::logger('utexas_google_search')->notice('Deleting legacy search block form %bid', [
          '%bid' => $block_id,
        ]);
        $block->delete();
        // Create new Utexas Google Search block (/admin/structure/block).
        $new = \Drupal::service('entity_type.manager')
          ->getStorage('block')
          ->create([
            'id' => $block_id,
            'theme' => $theme,
            'region' => $region,
            'weight' => $weight,
            'provider' => 'utexas_google_search',
            'plugin' => 'utexas_google_search',
            'settings' => [
              'id' => 'utexas_google_search',
              'label' => $settings['label'] ?? 'Google Search',
              'label_display' => 0,
              'provider' => 'utexas_google_search',
            ],
            'visibility' => $visibility,
          ]);
        \Drupal::logger('utexas_google_search')->notice('Creating new Utexas Google Search block %bid', [
          '%bid' => $new->id(),
        ]);
        $new->save();
      }
    }
  }

}
