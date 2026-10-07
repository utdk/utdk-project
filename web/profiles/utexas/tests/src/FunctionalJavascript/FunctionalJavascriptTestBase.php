<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Element\TraversableElement;
use Drupal\Core\Database\Database;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\contextual\FunctionalJavascript\ContextualLinkClickTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Base class for Functional Javascript tests.
 */
abstract class FunctionalJavascriptTestBase extends WebDriverTestBase {

  // Core utility traits.
  use ContextualLinkClickTrait;
  use StringTranslationTrait;
  use TestFileCreationTrait;

  // UTexas utility traits.
  use InstallTestTrait;
  use EntityTestTrait;
  use UserTestTrait;

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * Specify the theme to be used in testing.
   *
   * @var string
   */
  protected $defaultTheme = 'speedway';

  /**
   * A user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testAdminUser;

  /**
   * A user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testContentEditorUser;

  /**
   * A user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testSiteManagerUser;

  /**
   * An image uri to be used with file uploads.
   *
   * @var string
   */
  protected $testImageId;

  /**
   * An video Media ID to be used with file rendering.
   *
   * @var string
   */
  protected $testVideoId;

  /**
   * The where screenshots will be saved.
   *
   * @var string
   */
  protected $screenshotPath;

  /**
   * The default timeout value.
   *
   * @var int
   */
  private $timeout;

  /**
   * Toggle value for https://www.drupal.org/node/3221100.
   *
   * @var bool
   */
  protected $failOnJavascriptConsoleErrors = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();

    $this->testAdminUser = $this->initializeAdminUser();
    $this->testContentEditorUser = $this->initializeContentEditor();
    $this->testSiteManagerUser = $this->initializeSiteManager();

    $this->drupalLogin($this->testAdminUser);
    $this->testImageId = $this->createTestMediaImage();
    $this->testVideoId = $this->createTestMediaVideoExternal();

    // When needed, create screenshots using something like below.
    // @code $this->createScreenshot($this->screenshotPath . 'description.png');
    $this->screenshotPath = \Drupal::root() . '/sites/default/files/simpletest/';

    // 20 seconds.
    $this->setTimeout(20000);
    $this->getSession()->resizeWindow(1200, 4000);
  }

  /**
   * {@inheritdoc}
   */
  protected function cleanupEnvironment() {
    // We override the cleanupEnvironment method from core's BrowserTestBase
    // due to issues with Docker Desktop. See below.
    // Remove all prefixed tables.
    $original_connection_info = Database::getConnectionInfo('simpletest_original_default');
    $original_prefix = $original_connection_info['default']['prefix'];
    $test_connection_info = Database::getConnectionInfo('default');
    $test_prefix = $test_connection_info['default']['prefix'];
    if ($original_prefix != $test_prefix) {
      $tables = Database::getConnection()->schema()->findTables('%');
      foreach ($tables as $table) {
        if (Database::getConnection()->schema()->dropTable($table)) {
          unset($tables[$table]);
        }
      }
    }

    // We skip the following, which causes issues when used with
    // Docker Desktop. Instead, the test execution command will perform the
    // cleanup. See utdk_localdev#99.
    // @codingStandardsIgnoreLine
    // \Drupal::service('file_system')->deleteRecursive($this->siteDirectory, [$this, 'filePreDeleteCallback']);
  }

  /**
   * Updates a block configuration on a layout builder enabled node page.
   *
   * @param string $block_name
   *   The name of the block to be added.
   * @param array $form_values
   *   The array for form values to be updated.
   * @param string|null $inline_block_plugin_id
   *   Provide if block is an inline block.
   * @param string|null $inline_block_index
   *   Provide if multiple blocks with same $inline_block_plugin_id may exist.
   */
  protected function updateBlockOnFlexPage($block_name, array $form_values = [], $inline_block_plugin_id = NULL, $inline_block_index = 1) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    $contextual_link_selector = $this->getBlockContextualLinkSelector($block_name, $inline_block_plugin_id, $inline_block_index);
    $this->clickContextualLink($contextual_link_selector, 'Edit');

    $this->switchToLayoutBuilderIframe();
    $form_id = 'layout-builder-update-block';
    $form = $this->waitForForm($form_id);

    // Find element within parent.
    $selector = '//input[@value="Update"]';
    $input = $form->find('xpath', $selector);
    $this->assertNotEmptyXpath($input, $selector);

    $this->scrollElementIntoView($input);
    $this->submitForm($form_values, 'Update', $form_id);
    $this->switchFromLayoutBuilderIframe();

    // Wait for element to go away.
    $message = 'Element exists on the page. xpath: ' . $selector;
    $assert->assertNoElementAfterWait('xpath', $input->getXpath(), $this->getTimeout(), $message);
  }

  /**
   * Creates an inline block on a layout builder enabled node page.
   *
   * @param string $block_type_label
   *   The name of the block to be added.
   * @param array $form_values
   *   The name of the block to be added.
   */
  protected function createInlineBlockOnFlexPage($block_type_label, array $form_values = []) {
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();
    $this->scrollLinkIntoViewAndClick($page, 'Add block');
    $this->waitForUiDialogTitle('Choose a block');
    $this->clickLink('Create content block');
    $this->waitForUiDialogTitle('Add a new content block');
    $this->clickLink($block_type_label);
    $this->switchToLayoutBuilderIframe();
    $form_id = 'layout-builder-add-block';
    $this->waitForForm($form_id);
    $this->submitForm($form_values, 'Add block', $form_id);
    $this->switchFromLayoutBuilderIframe();
  }

  /**
   * Places an existing reusable block on a layout builder enabled node page.
   *
   * @param \Behat\Mink\Element\NodeElement $form
   *   The parent form where we want to remove media item from.
   * @param string $block_name
   *   The name of the block to be added.
   */
  protected function placeExistingBlockOnFlexPage(NodeElement $form, $block_name) {
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    $this->scrollLinkIntoViewAndClick($page, 'Add block');

    $this->waitForUiDialogTitle('Choose a block');
    $this->scrollLinkIntoViewAndClick($page, $block_name);

    // Wait for block form and save it.
    $this->waitForUiDialogTitle('Configure block');
    $this->switchToLayoutBuilderIframe();
    $form = $this->waitForForm('layout-builder-add-block');
    $this->clickInputByValue($form, 'Add block', TRUE);
    $this->switchFromLayoutBuilderIframe();
  }

  /**
   * Adds an Image media item to a form using the media library.
   *
   * Note that the button_index should be used if there is more than one
   * "Add media" button on the page.
   *
   * @param string|null $media_name
   *   An optional media entity name.
   * @param string|null $button_index
   *   Provide if a button besides the first match is needed.
   */
  protected function addMediaLibraryImage($media_name = 'image-test.png', $button_index = 1) {
    $this->addMediaLibraryItem($media_name, 'utexas_image', 'Image', $button_index);
  }

  /**
   * Adds an External Video media item to a field using the media library.
   *
   * Note that the button_index should be used if there is more than one
   * "Add media" button on the page.
   *
   * @param string $media_name
   *   (Optional) An optional media entity name.
   * @param string $button_index
   *   (Optional) Provide if a button besides the first match is needed.
   */
  protected function addMediaLibraryExternalVideo($media_name = 'Video 1', $button_index = 1) {
    $this->addMediaLibraryItem($media_name, 'utexas_video_external', 'Video (External)', $button_index);
  }

  /**
   * Adds a media item to a form field using the media library.
   *
   * @param string $media_name
   *   An optional media entity name.
   * @param string $media_type
   *   (Optional) The machine name of the media type. Only needed if there are
   *   more than one media types allowed.
   * @param string $media_type_link_text
   *   (Optional) The link text for the media type. Only needed if there are
   *   more than one media types allowed.
   * @param string $button_index
   *   (Optional) Provide if a button besides the first match is needed.
   */
  protected function addMediaLibraryItem($media_name, $media_type = NULL, $media_type_link_text = NULL, $button_index = 1) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    $this->clickInputByValue($page, 'Add media', FALSE, $button_index);

    // Wait for modal.
    $dialog = $this->waitForUiDialogTitle('Add or select media');

    // Select media tab. This should only happen if more than one media type is
    // available for selection.
    $selector = '//ul[contains(@class, "media-library-menu")]';
    $media_type_menu = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    if (!empty($media_type_menu)) {
      $this->scrollLinkIntoViewAndClick($dialog, $media_type_link_text);
    }

    // Wait for media type list form.
    $text = 'media_library_selected_type=' . $media_type;
    $selector = $assert->buildXPathQuery(
      '(//form[contains(@action, :text)])[last()]',
      [':text' => $text]
    );
    $form = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($form, $selector);

    // Wait on media item matching the media_name and select.
    $this->clickInputByLabel($form, 'Select ' . $media_name);

    // Verify media item has been selected.
    $selector = '//div[contains(@class, "js-media-library-item")][contains(@class, "checked")]';
    $item = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($item, $selector);
    $assert->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Insert selected');

    // Verify media has been inserted.
    $selector = '//input[contains(@class, "media-library-item__remove")]';
    $remove_button = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($remove_button, $selector);
  }

  /**
   * Removes a media item from a specified form.
   *
   * @param \Behat\Mink\Element\NodeElement $form
   *   The parent form where we want to remove media item from.
   * @param string|null $input_index
   *   Provide if an input besides the first match is needed.
   */
  protected function removeMediaLibraryItem(NodeElement $form, $input_index = 1): void {
    $this->clickInputByValue($form, 'Remove', TRUE, $input_index);
  }

  /**
   * Adds a link item to a form.
   *
   * @param \Behat\Mink\Element\NodeElement $form
   *   The parent form to the "Add link" button.
   * @param string $button_text
   *   The text of the "add another" button.
   * @param int $button_index
   *   (Optional) Provide if a button besides the first match is needed.
   */
  protected function addNonDraggableFormItem(NodeElement $form, $button_text, $button_index = 1) {
    // Delay clicking to allow for JS actions to complete (e.g., Linkit).
    sleep(1);
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    $args = [
      ':text' => $button_text,
      ':index' => $button_index,
    ];

    // Create form item xpath query.
    $form_item_selector = '(//input[contains(@value, :text)])[:index]';
    $item_selector = $assert->buildXPathQuery(
      $form_item_selector,
      $args
    );

    // Create form items list xpath query.
    $form_items_selector = $form_item_selector . '/preceding-sibling::div';
    $items_selector = $assert->buildXPathQuery(
      $form_items_selector,
      $args
    );

    $this->addFormItem(
      $form,
      $item_selector,
      $items_selector
    );
  }

  /**
   * Adds a draggable item to a form.
   *
   * @param \Behat\Mink\Element\NodeElement $form
   *   The parent form to the "Add another item" button.
   * @param string $button_text
   *   The text of the "add another" button.
   * @param int $button_index
   *   (Optional) Provide if a button besides the first match is needed.
   */
  protected function addDraggableFormItem(NodeElement $form, $button_text, $button_index = 1) {
    // Delay clicking to allow for JS actions to complete (e.g., Linkit).
    sleep(1);
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Create form item xpath query.
    $args = [
      ':text' => $button_text,
      ':index' => $button_index,
    ];
    $form_item_selector = '(//input[contains(@value, :text)])[:index]';
    $item_selector = $assert->buildXPathQuery(
      $form_item_selector,
      $args
    );

    // Create form items list xpath query.
    $form_items_selector = '(' . $form_item_selector . '/ancestor::div/table)[last()]/tbody/tr';
    $items_selector = $assert->buildXPathQuery(
      $form_items_selector,
      $args
    );

    $this->addFormItem(
      $form,
      $item_selector,
      $items_selector
    );
  }

  /**
   * Adds a link item to a form.
   *
   * @param \Behat\Mink\Element\NodeElement $form
   *   The parent form to the "Add another item" button.
   * @param string $item_selector
   *   The selector for the form item.
   * @param string $items_selector
   *   The selector for the list of items.
   */
  protected function addFormItem(NodeElement $form, $item_selector, $items_selector) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Find button.
    $add_item_button = $form->find('xpath', $item_selector);
    $this->assertNotEmptyXpath($add_item_button, $item_selector);

    // Ensure that element is visible.
    $this->scrollElementIntoView($add_item_button);

    // Count current items.
    $items = $form->findAll('xpath', $items_selector);
    $items_count = count($items);

    // Find out what the index of the newly added item will be.
    $new_item_index = $items_count + 1;

    // Create selector that will find newly added item.
    $selector3 = $items_selector . '[' . $new_item_index . ']';

    // Add the new item.
    $add_item_button->press();

    // Wait for new item to be added.
    $new_item = $assert->waitForElement('xpath', $selector3, $this->getTimeout());
    $this->assertNotEmptyXpath($new_item, $selector3);
  }

  /**
   * Wait for the "Configure Section" for title to appear.
   *
   * @param string $section_label
   *   The node ID of the Layout Builder enabled page in question.
   */
  protected function openSectionConfiguration($section_label): void {
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    $this->scrollLinkIntoViewAndClick($page, 'Configure ' . $section_label);
    $this->waitForUiDialogTitle('Configure section');
    $this->switchToLayoutBuilderIframe();
  }

  /**
   * Wait for a UI dialog title to appear.
   *
   * This is a decent way to wait for a pseudo-form to appear. A pseudo-form is
   * a collection of links that have a similar function to a form, but are not
   * contained by a <form> element.
   *
   * @param string $text
   *   The text of the dialog title.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The UI Dialog element containing the title.
   */
  protected function waitForUiDialogTitle($text) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    $selector = $assert->buildXPathQuery(
      '//*[contains(@class, "ui-dialog-title")][text()=:text]',
      [':text' => $text]
    );
    $ui_dialog = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($ui_dialog, $selector);

    return $ui_dialog;
  }

  /**
   * Wait for the UI dialog title to appear.
   *
   * @param string $form_id
   *   The partial or complete form id.
   * @param string|null $form_index
   *   Provide if an input besides the first match is needed.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The form element.
   */
  protected function waitForForm($form_id, $form_index = 1) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    $substitutions = [
      ':text' => $form_id,
      ':index' => $form_index,
    ];
    $selector = $assert->buildXPathQuery(
      '(//form[contains(@id, :text)])[:index]',
      $substitutions
    );
    $form = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($form, $selector);

    return $form;
  }

  /**
   * Saves section configuration.
   */
  protected function saveSectionConfiguration(): void {
    $form = $this->waitForForm('layout-builder-configure-section');
    $this->clickInputByValue($form, 'Update', TRUE);
    $this->switchFromLayoutBuilderIframe();
  }

  /**
   * Saves page layout.
   */
  protected function savePageLayout(): void {
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->clickInputByValue($form, 'Save layout', TRUE);
  }

  /**
   * Click a details element identified by text in its summary.
   *
   * @param string $summary_text
   *   The text of a summary element or the text of one of its children.
   * @param int $index
   *   The index of the summary element if more than one are expected to match
   *   the summary_text provided. First element = 1.
   */
  protected function clickDetailsBySummaryText($summary_text, $index = 1): void {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Get xpath for the preceding <details> element.
    $details_xpath_query = '((//details/summary[contains(., :text)])[:index]/..)';
    $substitutions = [
      ':text' => $summary_text,
      ':index' => $index,
    ];
    $details_xpath = $assert->buildXPathQuery(
      $details_xpath_query,
      $substitutions
    );

    // Scroll to details element.
    $details = $assert->waitForElement('xpath', $details_xpath, $this->getTimeout());
    $this->assertNotEmptyXpath($details, $details_xpath);
    $this->scrollElementIntoView($details);

    // Note that details element must be "not open" to be clicked.
    $details_closed_xpath_query = $details_xpath . '[not(@open)]';
    $closed_details = $assert->waitForElement('xpath', $details_closed_xpath_query, $this->getTimeout());
    $this->assertNotEmptyXpath($closed_details, $details_closed_xpath_query);
    $closed_details->click();

    // Wait for the <details> element to "open".
    $details_open_xpath_query = $details_xpath . '[@open]';
    $open_details = $assert->waitForElement('xpath', $details_open_xpath_query, $this->getTimeout());
    $this->assertNotEmptyXpath($open_details, $details_open_xpath_query);
  }

  /**
   * Click an element identified by its name.
   *
   * @param \Behat\Mink\Element\TraversableElement $parent_element
   *   The parent element to the input.
   * @param string $name_text
   *   The name of an element.
   * @param bool|null $no_element_after_wait
   *   Indicate whether the element is expected to be removed after clicking.
   * @param int|null $input_index
   *   Provide if an input element besides the first match is needed.
   */
  protected function clickElementByName(TraversableElement $parent_element, $name_text, $no_element_after_wait = FALSE, $input_index = 1): void {
    // Delay clicking to allow for JS actions to complete (e.g., Linkit).
    sleep(1);
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $xpath = '//*[@name=:value]';
    $selector = $assert->buildXPathQuery(
      $xpath,
      [':value' => $name_text]
    );
    $input = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($input, $selector);
    $this->scrollInputIntoViewAndClick($parent_element, '@id', $input->getAttribute('id'), $no_element_after_wait, $input_index);
  }

  /**
   * Click an input element identified by its label.
   *
   * @param \Behat\Mink\Element\TraversableElement $parent_element
   *   The parent element to the input.
   * @param string $label_text
   *   The text of a checkbox field label.
   * @param bool|null $no_element_after_wait
   *   Indicate whether the element is expected to be removed after clicking.
   * @param int|null $input_index
   *   Provide if an input element besides the first match is needed.
   */
  protected function clickInputByLabel(TraversableElement $parent_element, $label_text, $no_element_after_wait = FALSE, $input_index = 1): void {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // The label can be either before or after the input depending on form
    // element settings. This should almost always work because form elements
    // use have a wrapper around the label/input that keeps it separate from
    // other input elements.
    $selector = $assert->buildXPathQuery(
      '//label[text() = :text]/preceding-sibling::input | //label[text() = :text]/following-sibling::input',
      [':text' => $label_text]
    );
    $input = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($input, $selector);

    $this->scrollInputIntoViewAndClick($parent_element, '@id', $input->getAttribute('id'), $no_element_after_wait, $input_index);
  }

  /**
   * Click an input element identified by its value attribute.
   *
   * @param \Behat\Mink\Element\TraversableElement $parent_element
   *   The parent element to the input.
   * @param string $value
   *   The text of a checkbox field label.
   * @param bool|null $no_element_after_wait
   *   Indicate whether the element is expected to be removed after clicking.
   * @param int|null $input_index
   *   Provide if an input element besides the first match is needed.
   */
  protected function clickInputByValue(TraversableElement $parent_element, $value, $no_element_after_wait = FALSE, $input_index = 1): void {
    $this->scrollInputIntoViewAndClick($parent_element, '@value', $value, $no_element_after_wait, $input_index);
  }

  /**
   * Select a dropdown selection by its text.
   *
   * @param string $option_text
   *   The text inside a <select> <option> element.
   */
  protected function selectFieldOptionByOptionText($option_text): void {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Get the <select> element by finding the option.
    $selector = $assert->buildXPathQuery(
      '//option[text() = :text]/..',
      [':text' => $option_text]
    );
    $select = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($select, $selector);

    $select->selectOption($option_text);
  }

  /**
   * Load layout tab page for a layout builder enabled content type.
   *
   * @param string $node_id
   *   The node ID of the Layout Builder enabled page in question.
   */
  protected function drupalGetNodeLayoutTab($node_id): void {
    $this->drupalGet('node/' . $node_id . '/layout');
  }

  /**
   * Adds section to layout UI.
   *
   * @param string $section_layout
   *   The layout for the section to be added.
   */
  protected function addSectionToLayoutBuilder($section_layout) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    // @codingStandardsIgnoreLine
    $assert = $this->assertSession();

    $this->clickLink('Add section');

    // Wait for "section add" modal to OPEN.
    $this->waitForUiDialogTitle('Choose a layout for this section');

    // Click section layout.
    $this->clickLink($section_layout);

    $this->waitForUiDialogTitle('Configure section');
    $this->switchToLayoutBuilderIframe();
    $form = $this->waitForForm('layout-builder-configure-section');
    // Add new section.
    $this->clickInputByValue($form, 'Add section', TRUE);
    $this->switchFromLayoutBuilderIframe();
  }

  /**
   * Removes section from layout UI.
   *
   * @param string $section_label
   *   The section to be removed.
   */
  protected function removeSectionFromLayoutBuilder($section_label) {
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Click link to remove section.
    $this->scrollLinkIntoViewAndClick($page, 'Remove ' . $section_label);

    $this->switchToLayoutBuilderIframe();
    $form = $this->waitForForm('layout-builder-remove-section');
    $this->clickInputByValue($form, 'Remove', TRUE);
    $this->switchFromLayoutBuilderIframe();
  }

  /**
   * Use JS to scroll a link element into view and "click" it.
   *
   * @param \Behat\Mink\Element\TraversableElement $parent_element
   *   The parent form to the button.
   * @param string $link_text
   *   The text within the target link element.
   * @param bool|null $no_element_after_wait
   *   Indicate whether the element is expected to be removed after clicking.
   * @param int|null $link_index
   *   Provide if an link element besides the first match is needed.
   */
  protected function scrollLinkIntoViewAndClick(TraversableElement $parent_element, $link_text, $no_element_after_wait = FALSE, $link_index = 1) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    $selector = $this->scrollElementIntoViewAndClick($parent_element, 'a', '', $link_text, $link_index);

    if ($no_element_after_wait) {
      // Wait for input element to go away.
      $message = 'Element exists on the page. xpath: ' . $selector;
      $assert->assertNoElementAfterWait('xpath', $selector, $this->getTimeout(), $message);
    }
  }

  /**
   * Use JS to scroll an input element into view and "press" it.
   *
   * @param \Behat\Mink\Element\TraversableElement $parent_element
   *   The parent element to the button.
   * @param string $input_attribute
   *   The attribute of the target input element to test.
   * @param string $input_value
   *   The attribute value of the target input element.
   * @param bool|null $no_element_after_wait
   *   Indicate whether the element is expected to be removed after clicking.
   * @param int|null $input_index
   *   Provide if an input element besides the first match is needed.
   */
  protected function scrollInputIntoViewAndClick(TraversableElement $parent_element, $input_attribute, $input_value, $no_element_after_wait = TRUE, $input_index = 1) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    $selector = $this->scrollElementIntoViewAndClick($parent_element, 'input', $input_attribute, $input_value, $input_index);

    if ($no_element_after_wait) {
      // Wait for input element to go away.
      $message = 'Element exists on the page. xpath: ' . $selector;
      $assert->assertNoElementAfterWait('xpath', $selector, $this->getTimeout(), $message);
    }

  }

  /**
   * Use JS to scroll an element into view and "click" it.
   *
   * @param \Behat\Mink\Element\TraversableElement $parent_element
   *   A parent element to the target.
   * @param string $element_name
   *   The name of the HTML element.
   * @param string $attribute_name
   *   The name of the attribute to check.
   * @param string $attribute_text
   *   The text within the target attribute.
   * @param int|null $element_index
   *   Provide if an element besides the first match is needed.
   *
   * @return string
   *   The selector used to find the element.
   */
  protected function scrollElementIntoViewAndClick(TraversableElement $parent_element, $element_name, $attribute_name, $attribute_text, $element_index = 1) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Build element xpath query.
    $substitutions = [
      ':text' => $attribute_text,
      ':index' => $element_index,
    ];
    if (!empty($attribute_name)) {
      $xpath_query = '(//' . $element_name . '[' . $attribute_name . '=:text])[:index]';
    }
    else {
      $xpath_query = '(//' . $element_name . '[text()[contains(.,:text)]])[:index]';
    }
    $selector = $assert->buildXPathQuery($xpath_query, $substitutions);
    // Find element within parent.
    $element = $parent_element->find('xpath', $selector);
    if (!$element) {
      $this->assertEquals('An element with the following xpath should be found', $selector);
    }
    $this->assertNotEmptyXpath($element, $selector);

    // Ensure that element is visible.
    $this->scrollElementIntoView($element);

    // Wait on element again (though we really shouldn't need to).
    $element = $assert->waitForElement('xpath', $selector, $this->getTimeout());
    $this->assertNotEmptyXpath($element, $selector);

    $element->click();

    return $selector;
  }

  /**
   * Uses JS to scroll an element into view using its xpath selector.
   *
   * @param \Behat\Mink\Element\NodeElement|null $element
   *   The node element if it exists.
   */
  protected function scrollElementIntoView($element) {
    /** @var \Behat\Mink\Element\TraversableElement $element_parent */
    $element_parent = $element->getParent();

    $xpath = $element->getXpath();

    // Scroll to element.
    $script = "document.evaluate('" . $xpath . "', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.scrollIntoView();";
    $this->getSession()->executeScript($script);

    $is_visible = $element_parent->waitFor(1, function () use ($element) {
      return $element->isVisible();
    });

    if (!$is_visible) {
      throw new \Exception(sprintf('Dragging css class was not added on handle "%s".', $xpath));
    }
  }

  /**
   * Asserts that an element was returned after using the "xpath" selector type.
   *
   * @param \Behat\Mink\Element\NodeElement|null $element
   *   The node element if it exists.
   * @param string $selector
   *   The xpath selector.
   */
  protected function assertNotEmptyXpath(NodeElement $element, $selector) {
    $message = 'No element matching xpath selector "' . $selector . '" was found, or the element is otherwise unavailable.';
    $this->assertNotEmpty($element, $message);
  }

  /**
   * Get the timeout variable.
   *
   * @return int
   *   The timeout value.
   */
  protected function getTimeout() {
    return $this->timeout;
  }

  /**
   * Set the timeout variable.
   *
   * @param int $timeout
   *   The timeout value.
   */
  protected function setTimeout($timeout) {
    $this->timeout = $timeout;
  }

  /**
   * Gets contextual link selector based on block information.
   *
   * @param string $block_name
   *   The name of the block to be added.
   * @param string|null $inline_block_plugin_id
   *   Provide if block is an inline block.
   * @param string|null $inline_block_index
   *   Provide if block is an inline block.
   *
   * @return string
   *   The selector for the desired contextual link.
   */
  protected function getBlockContextualLinkSelector($block_name, $inline_block_plugin_id = NULL, $inline_block_index = 1) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    if ($inline_block_plugin_id) {
      $args = [
        ':text' => $inline_block_plugin_id,
        ':index' => $inline_block_index,
      ];
      $selector = $assert->buildXPathQuery(
        '//div[contains(@class, :text)][:index]',
        $args
      );
      $inline_block = $page->find('xpath', $selector);
      $this->assertNotEmptyXpath($inline_block, $selector);
      $uuid = $inline_block->getAttribute('data-layout-block-uuid');
      $contextual_link_selector = "[data-layout-block-uuid=\"$uuid\"]";
    }
    else {
      $content_block_prefix = '.block-block-content';
      $uuid = $this->drupalGetBlockByInfo($block_name)->uuid();
      $contextual_link_selector = $content_block_prefix . $uuid;
    }

    return $contextual_link_selector;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Remove this in https://www.drupal.org/project/drupal/issues/2918718.
   */
  protected function clickContextualLink($selector, $link_locator, $force_visible = TRUE) {
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();
    $page->waitFor(10, function () use ($page, $selector) {
      return $page->find('css', "$selector .contextual-links");
    });
    if (count($page->findAll('css', "$selector .contextual-links")) > 1) {
      throw new \Exception('More than one contextual links found by selector');
    }
    if ($force_visible && $page->find('css', "$selector .contextual .trigger.visually-hidden")) {
      $this->toggleContextualTriggerVisibility($selector);
    }

    $element = $page->find('css', $selector);
    $link = $element->findLink($link_locator);
    $this->assertNotEmpty($link);

    if (!$link->isVisible()) {
      $button = $page->waitFor(10, function () use ($element) {
        $button = $element->find('css', '.contextual button');
        return $button->isVisible() ? $button : FALSE;
      });
      $button->press();
      $link = $page->waitFor(10, function () use ($link) {
        return $link->isVisible() ? $link : FALSE;
      });
    }

    $link->click();

    if ($force_visible) {
      $this->toggleContextualTriggerVisibility($selector);
    }
  }

  /**
   * Switches to the layout builder iframe.
   */
  protected function switchToLayoutBuilderIframe(): void {
    /** @var \Drupal\FunctionalJavascriptTests\JSWebAssertWebAssert $assert */
    $assert = $this->assertSession();
    $assert->waitForElementVisible('css', '#drupal-lbim-modal');
    $this->getSession()->switchToIFrame('lbim-dialog-iframe');
  }

  /**
   * Switches from the layout builder iframe.
   */
  protected function switchFromLayoutBuilderIframe(): void {
    $this->getSession()->switchToIFrame();
    /** @var \Drupal\FunctionalJavascriptTests\JSWebAssert $assert */
    $assert = $this->assertSession();
    $this->assertTrue($assert->waitForElementRemoved('css', '#drupal-lbim-modal'));
  }

}
