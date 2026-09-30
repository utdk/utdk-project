<?php

namespace Drupal\utexas_google_search\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\utexas_google_search\Form\UtexasGoogleSearchForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * The route controller for Google PSE Search.
 */
class Search extends ControllerBase {

  /**
   * Creates a render array for the search page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   The search form and search results build array.
   */
  public function view(Request $request) {
    // We allow static calls to Drupal methods.
    // phpcs:ignore
    $google_pse_id = \Drupal::state()->get('utexas.google_pse_id') ?? '';
    $build['#cache']['contexts'][] = 'url.query_args:keys';
    $search_term = Html::escape($request->query->get('keys'));
    $build['#title']['#markup'] = !empty($search_term) ? "Search for <q>$search_term</q>" : "Search";
    // Add the Google Programmable Search library itself, with ID as a param.
    $build['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#attributes' => [
          'async' => '',
          'src' => 'https://cse.google.com/cse.js?cx=' . $google_pse_id,
        ],
      ],
      'utexas_google_search_' . $google_pse_id,
    ];
    $build['search_form'] = $this->formBuilder()->getForm(UtexasGoogleSearchForm::class);
    $build['search_form']['#attributes']['class'][] = 'in-page-google-search';
    $build['search_form']['suffix'] = [
      '#markup' => '<details id="edit-help-link"><summary>About searching<span class="summary"></span></summary><ul data-drupal-selector="edit-list"><li>Search looks for exact, case-insensitive keywords; keywords shorter than a minimum length are ignored.</li><li>Use upper-case OR to get more results. Example: cat OR dog (content contains either "cat" or "dog").</li><li>You can use upper-case AND to require all words, but this is the same as the default behavior. Example: cat AND dog (same as cat dog, content must contain both "cat" and "dog").</li><li>Use quotes to search for a phrase. Example: "the cat eats mice".</li><li>You can precede keywords by - to exclude them; you must still have at least one "positive" keyword. Example: cat -dog (content must contain cat and cannot contain dog).</li></ul></details>',
    ];
    $build['search_results'] = [
      '#theme' => 'utexas_google_search_results',
      '#attached' => [
        'library' => [
          'utexas_google_search/accessibilityTweaks',
        ],
      ],
    ];
    // Noscript fallback.
    $url = Url::fromUri('https://cse.google.com/cse', [
      'query' => [
        'cx' => $google_pse_id,
        'q' => $search_term,
      ],
    ]);
    $build['search_results']['#noscript'] = $this->t('@google, or enable JavaScript to view them here.', [
      '@google' => Link::fromTextAndUrl('View the results at Google', $url)->toString(),
    ]);
    return $build;
  }

}
