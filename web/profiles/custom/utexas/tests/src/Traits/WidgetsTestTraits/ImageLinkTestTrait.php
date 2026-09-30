<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Defines testing for Image Link widget.
 */
trait ImageLinkTestTrait {

  /**
   * Test schema.
   */
  public function verifyImageLink() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Image Link';
    $block_type_id = 'utexas_image_link';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Image Link fields.
    $this->addMediaLibraryImage();
    $form->fillField('field_block_il[0][link][uri]', 'https://imagelink.test');
    $form->fillField('field_block_il[0][link][title]', 'Alt value');
    $form->fillField('field_block_il[0][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_il[0][link][options][attributes][class]', 'ut-cta-link--external');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/1/edit/usage');
    $assert->pageTextContains('Content block: Image Link');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->linkByHrefExists('https://imagelink.test');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://imagelink.test"] picture img', 'src', $expected_path);
    // Verify responsive image alt attribute is pulled from link title.
    $assert->elementAttributeContains('css', 'a[href^="https://imagelink.test"] picture img', 'alt', 'Alt value');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.utexas-image-link .ut-cta-link--external', 'rel', 'noopener noreferrer');
    $assert->elementExists('css', '.ut-cta-link--external');

    // CRUD: UPDATE
    // Test internal links.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Fill Image Link fields.
    $form->fillField('field_block_il[0][link][uri]', '/node/' . $flex_page_id);
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ
    // Verify page output.
    // Verify responsive image is present within the link.
    $this->drupalGet('node/' . $flex_page_id);
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="/test-flex-page"] picture img', 'src', $expected_path);

    // CRUD: DELETE.
    $this->removeblocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

}
