<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use Drupal\utexas_readonly\ReadOnlyHelper;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies specific Field UI pages are read-only.
 */
#[RunTestsInSeparateProcesses]
class ReadOnlyTest extends FunctionalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   *
   * @see Drupal\Tests\BrowserTestBase
   */
  protected static $modules = [
    'views_ui',
    'field_ui',
  ];

  /**
   * Test Read Only functionality.
   */
  public function testReadOnly() {
    $this->drupalLogin($this->initializeSuperAdminUser());

    $this->verifyLockedFields();
    $this->verifyReadOnlyPages();
  }

  /**
   * Test default configuration.
   */
  public function verifyLockedFields() {
    $locked_field_storage = [
      'block_content.field_utexas_sl_social_links',
      'block_content.field_utexas_call_to_action_link',
      'node.field_flex_page_metatags',
      'block_content.field_block_featured_highlight',
      'block_content.field_block_fca',
      'block_content.field_block_hero',
      'block_content.field_utexas_flex_list_items',
      'block_content.field_block_il',
      'media.field_media_file',
      'media.field_media_oembed_video',
      'media.field_utexas_media_image',
      'block_content.field_block_pca',
      'block_content.field_block_pl',
      'block_content.field_block_pu',
      'block_content.field_block_ql',
      'block_content.field_block_resources',
    ];
    foreach ($locked_field_storage as $field_storage) {
      $config = $this->config('field.storage.' . $field_storage);
      // Verify all fields are unlocked in configuration.
      $this->assertEquals($config->get('locked'), FALSE);
    }

  }

  /**
   * Test which pages admin users have access to.
   */
  public function verifyReadOnlyPages() {
    $assert = $this->assertSession();

    $read_only_text = 'This component is read-only and should not be modified.';

    $utexas_image_styles = [
      'utexas_image_style_1000w',
      'utexas_image_style_1000w_600h',
      'utexas_image_style_1000w_666h',
      'utexas_image_style_112w_112h',
      'utexas_image_style_1140w_616h',
      'utexas_image_style_1200w',
      'utexas_image_style_1200w_750h',
      'utexas_image_style_120w_150h',
      'utexas_image_style_128w_128h',
      'utexas_image_style_1350w',
      'utexas_image_style_140w_140h',
      'utexas_image_style_1440w_778h',
      'utexas_image_style_150w_188h',
      'utexas_image_style_1600w',
      'utexas_image_style_1600w_500h',
      'utexas_image_style_170w_170h',
      'utexas_image_style_176w_112h',
      'utexas_image_style_1800w',
      'utexas_image_style_1800w_2400h',
      'utexas_image_style_1920w_1038h',
      'utexas_image_style_2000w',
      'utexas_image_style_220w_140h',
      'utexas_image_style_2250w_900h',
      'utexas_image_style_2280w_1232h',
      'utexas_image_style_250w_150h',
      'utexas_image_style_280w_152h',
      'utexas_image_style_280w_280h',
      'utexas_image_style_300w_376h',
      'utexas_image_style_3200w',
      'utexas_image_style_3200w_1000h',
      'utexas_image_style_330w_200h',
      'utexas_image_style_340w_227h',
      'utexas_image_style_400w_250h',
      'utexas_image_style_440w_280h',
      'utexas_image_style_450w_300h',
      'utexas_image_style_450w_600h',
      'utexas_image_style_500w',
      'utexas_image_style_500w_300h',
      'utexas_image_style_500w_333h',
      'utexas_image_style_500w_500h',
      'utexas_image_style_600w',
      'utexas_image_style_600w_375h',
      'utexas_image_style_64w_64h',
      'utexas_image_style_660w_400h',
      'utexas_image_style_675w',
      'utexas_image_style_680w_454h',
      'utexas_image_style_720w_389h',
      'utexas_image_style_800w_500h',
      'utexas_image_style_85w_85h',
      'utexas_image_style_900w',
      'utexas_image_style_900w_1200h',
      'utexas_image_style_900w_600h',
      'utexas_image_style_960w_519h',
    ];

    // Pages that an admin user *should* have access to.
    $twohundred = [
      '/admin/structure/types/manage/page/fields',
      '/admin/structure/types/manage/page/form-display',
      '/admin/structure/types/manage/page/display',
      '/admin/structure/types/manage/page/fields/add-field',
      '/admin/structure/block-content/manage/basic/fields',
      '/admin/structure/block-content/manage/basic/fields/add-field',
      '/admin/structure/block-content/manage/basic/form-display',
      '/admin/structure/block-content/manage/basic/display',
      '/admin/structure/views/view/content',
      '/admin/structure/views/view/content/delete',
      '/admin/config/content/formats',
      '/admin/config/content/formats/manage/basic_html',
      '/admin/config/content/formats/manage/full_html',
      '/admin/config/content/formats/manage/restricted_html',
      '/admin/config/media/image-styles/manage/media_library',
      '/admin/config/media/image-styles/manage/medium',
      '/admin/config/media/image-styles/manage/medium/delete',
      '/admin/config/media/image-styles/manage/thumbnail',
      '/admin/config/media/image-styles/manage/thumbnail/delete',
      '/admin/config/media/image-styles/manage/large',
      '/admin/config/media/image-styles/manage/large/delete',
    ];
    // Users *should* be able to flush image styles provided by the kernel.
    foreach ($utexas_image_styles as $style) {
      $twohundred[] = '/admin/config/media/image-styles/manage/' . $style . '/flush';
    }
    foreach ($twohundred as $path) {
      $this->assertAllowed($path);
      $assert->pageTextNotContains($read_only_text);
    }
    // Pages that should be forbidden (403).
    $fourohthree = [];
    foreach (ReadOnlyHelper::$restrictedNodeTypes as $machine_name) {
      $fourohthree[] = '/admin/structure/types/manage/' . $machine_name . '/fields/add-field';
    }
    foreach (ReadOnlyHelper::$restrictedBlockTypes as $machine_name) {
      $fourohthree[] = '/admin/structure/block-content/manage/' . $machine_name . '/fields/add-field';
    }
    foreach ($fourohthree as $path) {
      $this->assertForbidden($path);
      $this->assertSession()->pageTextContains($read_only_text);
    }

    $read_only_paths = [];

    foreach (ReadOnlyHelper::$restrictedNodeTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/display';
    }
    foreach (ReadOnlyHelper::$restrictedBlockTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/block-content/manage/' . $machine_name . '/fields';
    }
    foreach (ReadOnlyHelper::$restrictedMediaTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/display';
    }
    // Image styles provided by the kernel.
    foreach ($utexas_image_styles as $style) {
      $read_only_paths[] = '/admin/config/media/image-styles/manage/' . $style;
      $read_only_paths[] = '/admin/config/media/image-styles/manage/' . $style . '/delete';
    }

    // Pages that should be read-only.
    foreach ($read_only_paths as $path) {
      if ($path === '/admin/structure/media/manage/utexas_document/display') {
        // @todo Skip until #3467501 is fixed (introduced in D10.3).
        continue;
      }
      $this->assertAllowed($path);
      $assert->pageTextContains($read_only_text);
    }
  }

}
