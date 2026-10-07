<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies Hero field schema & validation.
 */
trait HeroTestTrait {

  /**
   * Test schema.
   */
  public function verifyHero() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $session = $this->getSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $session->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Hero';
    $block_type_id = 'utexas_hero';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Hero fields.
    $form->fillField('field_block_hero[0][disable_image_styles]', '1');
    $this->addMediaLibraryImage();
    $form->fillField('field_block_hero[0][heading]', 'Hero Heading');
    $form->fillField('field_block_hero[0][subheading]', 'Hero Subheading');
    $form->fillField('field_block_hero[0][caption]', 'Hero Caption');
    $form->fillField('field_block_hero[0][credit]', 'Hero Credit');
    $form->fillField('field_block_hero[0][cta][link][uri]', 'https://hero.test');
    $form->fillField('field_block_hero[0][cta][link][title]', 'Hero CTA');
    $form->fillField('field_block_hero[0][cta][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_hero[0][cta][link][options][attributes][class]', 'ut-cta-link--external');

    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    $this->drupalGet('/media/1/edit/usage');
    $assert->pageTextContains('Content block: Hero');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Verify image styles are disabled.
    $this->drupalGet('node/' . $flex_page_id);
    $expected_path = '/files/image-test.png';
    $assert->elementAttributeContains('css', '.ut-hero img', 'src', $expected_path);
    // Verify page output.
    $assert->pageTextNotContains('Hero Heading');
    $assert->pageTextNotContains('Hero Subheading');
    $assert->pageTextContains('Hero Caption');
    $assert->pageTextContains('Hero Credit');
    // The default display does not include a CTA.
    $assert->linkByHrefNotExists('https://hero.test');
    $assert->pageTextNotContains('Hero CTA');

    // CRUD: UPDATE
    // Re-enable image styles.
    $this->drupalGet('admin/content/block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Fill Hero fields.
    $form->fillField('field_block_hero[0][disable_image_styles]', '0');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ
    // Verify responsive image is present within the link.
    $this->drupalGet('node/' . $flex_page_id);
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_720w_389h/public/image-test.png';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // CRUD: UPDATE
    // Set to Style 1.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->updateHeroBlockViewmodeSettings(['Style 1:', 'Center']);

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.hero--photo-orange-insert .hero-img');
    // Verify anchor class is set.
    $assert->elementExists('css', '.hero--photo-orange-insert .center');
    // Verify that the correct image style is being applied.
    // Since the screen width is 1200, we expect utexas_image_style_2250w_900h.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-orange-insert .hero-img").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);
    // The Hero style 1 does include a CTA.
    $assert->linkByHrefExists('https://hero.test');
    $assert->pageTextContains('Hero CTA');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-hero .ut-cta-link--external', 'rel', 'noopener noreferrer');

    // CRUD: UPDATE
    // Set display to "Hero Style 1 Left".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->updateHeroBlockViewmodeSettings(['Style 1:', 'Left']);

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.hero--photo-orange-insert .hero-img');
    // Verify anchor class is set.
    $assert->elementExists('css', '.hero--photo-orange-insert .left');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-orange-insert .hero-img").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // CRUD: UPDATE
    // Set display to "Hero Style 2".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->updateHeroBlockViewmodeSettings(['Style 2:', 'Center']);

    // CRUD: READ
    // Verify page output with anchor.
    $assert->elementExists('css', '.hero--photo-gradient');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-gradient").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // CRUD: UPDATE
    // Set display to "Hero Style 2 Right".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->updateHeroBlockViewmodeSettings(['Style 2:', 'Right']);

    // CRUD: READ
    // Verify page output with anchor.
    $assert->elementExists('css', '.hero--photo-gradient.right');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-gradient").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // CRUD: UPDATE
    // Set display to "Hero Style 3".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->updateHeroBlockViewmodeSettings(['Style 3:', 'Center']);

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--photo-white-notch');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".ut-hero.hero--photo-white-notch").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // CRUD: UPDATE
    // Set display to "Hero Style 4".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->updateHeroBlockViewmodeSettings(['Style 4:']);

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--blue-bar');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_720w_389h/public/image-test.png';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // CRUD: UPDATE
    // Set display to "Hero Style 5".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->updateHeroBlockViewmodeSettings(['Style 5:', 'Center']);

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--half-n-half');
    // Verify that the correct image style is being applied.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".ut-hero.hero--half-n-half .hero-img").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * Updates Hero block view mode.
   *
   * @param array $hero_updates
   *   The name of the block to be added.
   */
  private function updateHeroBlockViewmodeSettings(array $hero_updates) {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    $block_type = 'Hero';
    $block_name = $block_type . ' Test';
    $block_plugin_id = 'utexas-hero';

    $contextual_link_selector = $this->getBlockContextualLinkSelector($block_name, $block_plugin_id);
    $this->clickContextualLink($contextual_link_selector, 'Edit');

    $this->switchToLayoutBuilderIframe();
    $form_id = 'layout-builder-update-block';
    $form = $this->waitForForm($form_id);

    foreach ($hero_updates as $update) {
      $selector = $assert->buildXPathQuery(
        '//label//span[text()[contains(.,:text)]]/../preceding-sibling::input',
        [':text' => $update]
      );
      $input = $assert->waitForElement('xpath', $selector, $this->getTimeout());
      $this->assertNotEmptyXpath($input, $selector);

      $input->click();
    }

    // Submit form and save page.
    $this->clickInputByValue($form, 'Update');
    $this->switchFromLayoutBuilderIframe();
    $this->savePageLayout();
  }

}
