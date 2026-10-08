<?php

namespace Drupal\utnews_demo_content;

/**
 * PHP array of utnews node data.
 */
class DemoData {

  /**
   * {@inheritdoc}
   */
  public static function loadData() {
    $data = [
      'Maximal news article' => [
        'title' => 'Res maximus',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 1'],
        'field_utnews_news_tags' => ['Demo Tag 1', 'Demo Tag 2'],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-2 days midnight + 11 hours')),
        'field_utnews_external_link' => [
          'uri' => 'https://www.utexas.edu',
          'options' => [
            'attributes' => [
              'target' => [
                '_blank',
              ],
              'class' => 'ut-cta-link--external',
            ],
          ],
        ],
      ],
      'Minimal news article' => [
        'title' => 'Res minimas',
        'field_utnews_body' => [
          'value' => '',
          'summary' => 'Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ],
      ],
      'No sidebar news article' => [
        'title' => 'Null latus',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-6 days midnight + 11 hours')),
      ],
      'No author news article' => [
        'title' => 'Null auctor',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 1'],
        'field_utnews_news_tags' => ['Demo Tag 1', 'Demo Tag 2'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-4 days midnight + 1 hours')),
      ],
      'Author no image news article' => [
        'title' => 'Null auctor imago',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_article_author' => ['Demo Author 2'],
        'field_utnews_news_categories' => ['Demo Category 1'],
        'field_utnews_news_tags' => ['Demo Tag 1', 'Demo Tag 2'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-5 days midnight + 13 hours')),
      ],
      'No main image news article' => [
        'title' => 'Null pelagus imago',
        'field_utnews_display_image' => ['value' => FALSE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 2'],
        'field_utnews_news_tags' => ['Demo Tag 2'],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-9 days midnight')),
      ],
      'No publication date news article' => [
        'title' => 'Null promulgatio diem',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 2'],
        'field_utnews_news_tags' => ['Demo Tag 1', 'Demo Tag 2'],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => TRUE],
      ],
      'Generic article' => [
        'title' => 'Genus articulus',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 1'],
        'field_utnews_news_tags' => ['Demo Tag 2'],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-7 days midnight + 15 hours')),
      ],
      'Most recent news article' => [
        'title' => 'Recens articulus',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 1'],
        'field_utnews_news_tags' => ['Demo Tag 1', 'Demo Tag 2'],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('now')),
      ],
      'No body news article' => [
        'title' => 'Null corpus',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 2'],
        'field_utnews_news_tags' => ['Demo Tag 2'],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => TRUE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-10 days midnight + 3 hours')),
      ],
      'Non-featured news article' => [
        'title' => 'Non amet',
        'field_utnews_display_image' => ['value' => TRUE],
        'field_utnews_body' => [
          'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
          'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
          'format' => 'flex_html',
        ],
        'field_utnews_news_categories' => ['Demo Category 2'],
        'field_utnews_news_tags' => ['Demo Tag 1'],
        'field_utnews_article_author' => ['Demo Author 1'],
        'field_utnews_featured' => ['value' => FALSE],
        'field_utnews_publication_date' => date('Y-m-d', strtotime('-1 days midnight + 1 hours')),
      ],
    ];

    return $data;
  }

}
