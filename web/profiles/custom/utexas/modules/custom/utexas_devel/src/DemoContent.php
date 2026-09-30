<?php

namespace Drupal\utexas_devel;

use Drupal\layout_builder\Section;
use Drupal\node\Entity\Node;

/**
 * Demo Content generator.
 */
class DemoContent {

  /**
   * Helper function to save layout builder pages.
   */
  public static function createFromConfiguration($title, $block1, $block2) {
    $sections = [];
    $block1_id = md5('block' . $block1['block_revision_id']);
    $block2_id = md5('block' . $block2['block_revision_id']);
    // Reuse blocks 1 & 2 for blocks 3 & 4.
    $block3_id = md5('block3' . $block1['block_revision_id']);
    $block4_id = md5('block4' . $block1['block_revision_id']);

    // One column section.
    $section_array = [
      'layout_id' => 'layout_utexas_onecol',
      'components' => [
        $block1_id => [
          'uuid' => $block1_id,
          'region' => 'main',
          'configuration' => $block1,
          'additional' => [],
          'weight' => 0,
        ],
      ],
    ];
    $section_array_full_width = $section_array;
    $section_array_full_width['layout_settings'] = [
      'layout_builder_styles_style' => 'full_width_of_page',
    ];
    $sections[] = Section::fromArray($section_array);
    $sections[] = Section::fromArray($section_array_full_width);

    // Two column section.
    $twocol_widths_to_test = [
      '50-50',
      '33-67',
      '67-33',
      '25-75',
      '75-25',
    ];
    foreach ($twocol_widths_to_test as $width) {
      $section_array = [
        'layout_id' => 'layout_utexas_twocol',
        'layout_settings' => [
          'column_widths' => $width,
        ],
        'components' => [
          $block1_id => [
            'uuid' => $block1_id,
            'region' => 'first',
            'configuration' => $block1,
            'additional' => [],
            'weight' => 0,
          ],
          $block2_id => [
            'uuid' => $block2_id,
            'region' => 'second',
            'configuration' => $block2,
            'additional' => [],
            'weight' => 0,
          ],
        ],
      ];
      $section_array_full_width = $section_array;
      $section_array_full_width['layout_settings']['layout_builder_styles_style'] = 'full_width_of_page';
      $sections[] = Section::fromArray($section_array);
      $sections[] = Section::fromArray($section_array_full_width);
    }

    // Three column section.
    $threecol_widths_to_test = [
      '25-50-25',
      '33-34-33',
      '25-25-50',
      '50-25-25',
    ];
    foreach ($threecol_widths_to_test as $width) {
      $section_array = [
        'layout_id' => 'layout_utexas_threecol',
        'layout_settings' => [
          'column_widths' => $width,
        ],
        'components' => [
          $block1_id => [
            'uuid' => $block1_id,
            'region' => 'first',
            'configuration' => $block1,
            'additional' => [],
            'weight' => 0,
          ],
          $block2_id => [
            'uuid' => $block2_id,
            'region' => 'second',
            'configuration' => $block2,
            'additional' => [],
            'weight' => 0,
          ],
          $block3_id => [
            'uuid' => $block3_id,
            'region' => 'third',
            'configuration' => $block1,
            'additional' => [],
            'weight' => 0,
          ],
        ],
      ];
      $section_array_full_width = $section_array;
      $section_array_full_width['layout_settings']['layout_builder_styles_style'] = 'full_width_of_page';
      $sections[] = Section::fromArray($section_array);
      $sections[] = Section::fromArray($section_array_full_width);
    }

    // Four column section.
    $section_array = [
      'layout_id' => 'layout_utexas_fourcol',
      'components' => [
        $block1_id => [
          'uuid' => $block1_id,
          'region' => 'first',
          'configuration' => $block1,
          'additional' => [],
          'weight' => 0,
        ],
        $block2_id => [
          'uuid' => $block2_id,
          'region' => 'second',
          'configuration' => $block2,
          'additional' => [],
          'weight' => 0,
        ],
        $block3_id => [
          'uuid' => $block3_id,
          'region' => 'third',
          'configuration' => $block1,
          'additional' => [],
          'weight' => 0,
        ],
        $block4_id => [
          'uuid' => $block4_id,
          'region' => 'fourth',
          'configuration' => $block2,
          'additional' => [],
          'weight' => 0,
        ],
      ],
    ];
    $section_array_full_width = $section_array;
    $section_array_full_width['layout_settings'] = [
      'layout_builder_styles_style' => 'full_width_of_page',
    ];
    $sections[] = Section::fromArray($section_array);
    $sections[] = Section::fromArray($section_array_full_width);

    $node = Node::create(['type' => 'utexas_flex_page']);
    $node->set('title', $title);
    $node->setOwnerId(1);
    $node->set('layout_builder__layout', $sections);
    $node->setPublished(TRUE);
    $node->enforceIsNew();
    $node->save();
  }

  /**
   * Helper function for creating Featured Highlights.
   */
  public static function featuredHighlight($media_id) {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_featured_highlight',
      'reusable' => FALSE,
    ]);
    $block->info = 'Featured Highlight';
    $block->uuid = md5('featured_highlight_1' . time());
    $block->field_block_featured_highlight = [
      'headline' => "Featured Highlight",
      'media' => $media_id,
      'date' => "2019-06-12",
      "copy_value" => "Add descriptive text to provide a short summary of this featured content.",
      "link_uri" => "https://www.utexas.edu",
      "link_text" => "Visit UTexas",
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_featured_highlight',
      'label' => 'Featured Highlight',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_featured_highlight_3',
    ];

    $block = $blockEntityManager->create([
      'type' => 'utexas_featured_highlight',
      'reusable' => FALSE,
    ]);
    $block->info = 'Featured Highlight';
    $block->uuid = md5('featured_highlight_2' . time());
    $block->field_block_featured_highlight = [
      'headline' => "Timely Content",
      'media' => $media_id,
      'date' => "2020-01-31",
      "copy_value" => "Featured Highlights have a call to action & date field.",
      "link_uri" => "https://www.utexas.edu",
      "link_text" => "Gone to Texas",
    ];
    $block->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_featured_highlight',
      'label' => 'Featured Highlight',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_featured_highlight_2',
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

  /**
   * Helper function for creating Flex Content Areas.
   */
  public static function flexContentArea($media_id) {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_flex_content_area',
      'reusable' => FALSE,
    ]);
    $block->info = 'Flex Content Area';
    $block->uuid = md5('fca_1' . time());
    $block->field_block_fca[] = [
      'headline' => "Flex Content Area 1",
      'copy_value' => "The Flex Content Area has a number of display options.",
      'link_uri' => "https://utexas.edu",
      'link_text' => "Visit UTexas",
      'image' => $media_id,
    ];
    $block->field_block_fca[] = [
      'headline' => "Flex Content Area 2",
      'copy_value' => "Flex Content Areas may list links, or a call to action.",
      'link_uri' => "https://utexas.edu",
      'link_text' => "Hook 'em",
      'image' => $media_id,
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_flex_content_area',
      'label' => 'Flex_Content_Area',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_flex_content_area',
    ];

    $block2 = $blockEntityManager->create([
      'type' => 'utexas_flex_content_area',
      'reusable' => FALSE,
    ]);
    $block2->info = 'Flex Content Area';
    $block2->uuid = md5('fca_2' . time());
    $block2->field_block_fca[] = [
      'headline' => "Flex Content Area 3",
      'copy_value' => "They can display in 1, 2, 3, or 4 columns.",
      'link_uri' => "https://utexas.edu",
      'link_text' => "Gone to Texas",
      'image' => $media_id,
    ];
    $block2->field_block_fca[] = [
      'headline' => "Flex Content Area 4",
      'copy_value' => "Internal and external links can be used.",
      'link_uri' => "https://news.utexas.edu",
      'link_text' => "UTexas News",
      'image' => $media_id,
    ];
    $block2->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_flex_content_area',
      'label' => 'Flex_Content_Area',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block2->getRevisionId(),
      'view_mode' => 'utexas_flex_content_area',
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

  /**
   * Helper function for creating Hero styles.
   */
  public static function hero($media_id, $view_mode) {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_hero',
      'reusable' => FALSE,
    ]);
    $block->info = 'Hero';
    $block->uuid = md5($view_mode . '_1' . time());
    $block->field_block_hero = [
      'heading' => "Hero",
      'subheading' => "Subheading content",
      'media' => $media_id,
      "caption" => "A short caption may be added, describing the hero",
      "credit" => "Copyright University of Texas at Austin",
      "link_uri" => "https://www.utexas.edu",
      "link_title" => "Visit Texas",
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_hero',
      'label' => 'Hero',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => $view_mode,
    ];

    $block = $blockEntityManager->create([
      'type' => 'utexas_hero',
      'reusable' => FALSE,
    ]);
    $block->info = 'Hero';
    $block->uuid = md5($view_mode . '_2' . time());
    $block->field_block_hero = [
      'heading' => "New and Improved",
      'subheading' => "Heros can display in 6 styles",
      'media' => $media_id,
      "link_uri" => "https://www.utexas.edu",
      "link_title" => "Visit Texas",
      "caption" => "A short caption may be added, describing the hero",
      "credit" => "Copyright University of Texas at Austin",
    ];
    $block->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_hero',
      'label' => 'Hero',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => $view_mode,
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

  /**
   * Helper function for creating Photo Content Areas.
   */
  public static function photoContentArea($media_id) {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_photo_content_area',
      'reusable' => FALSE,
    ]);
    $block->info = 'Photo Content Area';
    $block->uuid = md5('pca_1' . time());
    $block->field_block_pca = [
      'headline' => "Photo Content Area",
      'copy_value' => "Photo content Areas include image, headline, credit, copy text, and links.",
      'links' => serialize([
        "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
        "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
        "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
      ]),
      'photo_credit' => "Ⓒ Photo by University of Texas at Austin",
      'image' => $media_id,
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_photo_content_area',
      'label' => 'Photo Content Area',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_photo_content_area',
    ];

    $block = $blockEntityManager->create([
      'type' => 'utexas_photo_content_area',
      'reusable' => FALSE,
    ]);
    $block->info = 'Photo Content Area';
    $block->uuid = md5('pca_2' . time());
    $block->field_block_pca = [
      'headline' => "Photo Content Area",
      'copy_value' => "All fields are optional",
      'links' => serialize([
        "0" => ['uri' => "https://www.utexas.edu", 'title' => "Undergraduate applications"],
        "1" => ['uri' => "https://www.utexas.edu", 'title' => "Graduate applications"],
        "2" => ['uri' => "https://www.utexas.edu", 'title' => "Post-doctoral fellowships"],
      ]),
      'image' => $media_id,
    ];
    $block->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_photo_content_area',
      'label' => 'Photo Content Area',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_photo_content_area',
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

  /**
   * Helper function for creating Promo Lists.
   */
  public static function promoList($media_id) {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_promo_list',
      'reusable' => FALSE,
    ]);
    $block->info = 'Promo List';
    $block->uuid = md5('promo_list_1' . time());
    $block->field_block_pl = [
      'headline' => "Promo List Group 1",
      "promo_list_items" => serialize([
        [
          'item' => [
            'headline' => "Item 1",
            'image' => $media_id,
            'copy' => [
              'value' => "Short descriptive text can be <b>formatted</b>.",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 2",
            'image' => $media_id,
            'copy' => [
              'value' => "Add informational text to describe the item",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 3",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo lists are unlimited",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 4",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo lists can be displayed in various column formats",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
      ]),
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_promo_list',
      'label' => 'Promo List',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_promo_list_2',
    ];

    $block = $blockEntityManager->create([
      'type' => 'utexas_promo_list',
      'reusable' => FALSE,
    ]);
    $block->info = 'Promo List';
    $block->uuid = md5('promo_list_2' . time());
    $block->field_block_pl = [
      'headline' => "Promo List Group 2",
      "promo_list_items" => serialize([
        [
          'item' => [
            'headline' => "Item 5",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo lists can be displayed in various column formats",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 6",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo lists are unlimited",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 7",
            'image' => $media_id,
            'copy' => [
              'value' => "Add informational text to describe the item",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 8",
            'image' => $media_id,
            'copy' => [
              'value' => "Add informational text to describe the item",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ],
      ]),
    ];
    $block->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_promo_list',
      'label' => 'Promo List',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_promo_list_2',
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

  /**
   * Helper function for creating Promo Units.
   */
  public static function promoUnit($media_id) {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_promo_unit',
      'reusable' => FALSE,
    ]);
    $block->info = 'Promo Unit';
    $block->uuid = md5('promo_unit_1' . time());
    $block->field_block_pu = [
      'headline' => "Promo Unit Group 1",
      "promo_unit_items" => serialize([
        [
          'item' => [
            'headline' => "Item 1",
            'image' => $media_id,
            'copy' => [
              'value' => "Short descriptive text can be <b>formatted</b>.",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 2",
            'image' => $media_id,
            'copy' => [
              'value' => "Add informational text to describe the item",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 3",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo units are unlimited",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 4",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo units can be displayed in various column formats",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
      ]),
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_promo_unit',
      'label' => 'Promo Unit',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_promo_unit',
    ];

    $block = $blockEntityManager->create([
      'type' => 'utexas_promo_unit',
      'reusable' => FALSE,
    ]);
    $block->info = 'Promo Unit';
    $block->uuid = md5('promo_unit_2' . time());
    $block->field_block_pu = [
      'headline' => "Promo Unit Group 2",
      "promo_unit_items" => serialize([
        [
          'item' => [
            'headline' => "Item 5",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo unit can be displayed in various column formats",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 6",
            'image' => $media_id,
            'copy' => [
              'value' => "Promo units are unlimited",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 7",
            'image' => $media_id,
            'copy' => [
              'value' => "Add informational text to describe the item",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Item 8",
            'image' => $media_id,
            'copy' => [
              'value' => "Add informational text to describe the item",
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => "https://www.utexas.edu",
              'title' => "Visit Texas",
            ],
          ],
        ],
      ]),
    ];
    $block->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_promo_unit',
      'label' => 'Promo Unit',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_promo_unit',
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

  /**
   * Helper function for creating Quick Links.
   */
  public static function quickLinks() {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_quick_links',
      'reusable' => FALSE,
    ]);
    $block->info = 'Quick Links';
    $block->uuid = md5('ql_1' . time());
    $block->field_block_ql = [
      'headline' => "Quick Links",
      'copy_value' => "Quick links include a headline, copy text, and links.",
      'links' => serialize([
        "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
        "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
        "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
      ]),
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_quick_links',
      'label' => 'Quick Links',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_quick_links',
    ];

    $block = $blockEntityManager->create([
      'type' => 'utexas_quick_links',
      'reusable' => FALSE,
    ]);
    $block->info = 'Quick Links';
    $block->uuid = md5('ql_2' . time());
    $block->field_block_ql = [
      'headline' => "Quick Links",
      'copy_value' => "Quick Links include a headline, copy text, and links.",
      'links' => serialize([
        "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
        "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
        "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
      ]),
    ];
    $block->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_quick_links',
      'label' => 'Quick Links',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_quick_links',
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

  /**
   * Helper function for creating Resources.
   */
  public static function resources($media_id) {
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_resources',
      'reusable' => FALSE,
    ]);
    $block->info = 'Resources';
    $block->uuid = md5('resource_1' . time());
    $block->field_block_resources = [
      'headline' => "Resource Group 1",
      "resource_items" => serialize([
        [
          'item' => [
            'headline' => "Resource 1",
            'image' => $media_id,
            'links' => [
              "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
              "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
              "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Resource 2",
            'image' => $media_id,
            'links' => [
              "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
              "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
              "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
            ],
          ],
        ],
      ]),
    ];
    $block->save();
    $configuration1 = [
      'id' => 'inline_block:utexas_resources',
      'label' => 'Resource',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_resource',
    ];

    $block = $blockEntityManager->create([
      'type' => 'utexas_resources',
      'reusable' => FALSE,
    ]);
    $block->info = 'Resources';
    $block->uuid = md5('resource_2' . time());
    $block->field_block_resources = [
      'headline' => "Resource Group 1",
      "resource_items" => serialize([
        [
          'item' => [
            'headline' => "Resource 1",
            'image' => $media_id,
            'links' => [
              "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
              "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
              "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
            ],
          ],
        ],
        [
          'item' => [
            'headline' => "Resource 2",
            'image' => $media_id,
            'links' => [
              "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
              "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
              "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
            ],
          ],
        ],
      ]),
    ];
    $block->save();
    $configuration2 = [
      'id' => 'inline_block:utexas_resources',
      'label' => 'Resource',
      'provider' => 'layout_builder',
      'label_display' => '0',
      'block_revision_id' => $block->getRevisionId(),
      'view_mode' => 'utexas_resource',
    ];
    return [
      'block1' => $configuration1,
      'block2' => $configuration2,
    ];
  }

}
