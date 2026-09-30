<?php

namespace Drupal\Tests\utevent\FunctionalJavascript;

use Drupal\Core\Language\Language;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Drupal\utevent\Permissions as UteventPermissions;
use Drupal\utexas\Permissions as UtexasPermissions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test all aspects of Event CRUD functionality.
 */
#[RunTestsInSeparateProcesses]
#[Group('utexas')]
class BasicUteventTest extends WebDriverTestBase {

  use TestFileCreationTrait;
  use NodeCreationTrait;
  use CKEditor5TestTrait;

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * Tests must specify what theme will be used.
   *
   * @var string
   */
  protected $defaultTheme = 'speedway';

  /**
   * The entity manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The test media ID.
   *
   * @var int
   */
  protected $testMediaImageId = 0;

  /**
   * The test media filename.
   *
   * @var string
   */
  protected $testMediaImageFilename = "";

  /**
   * A user with permissions to create Event content.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Modules to enable.
   *
   * @var array
   *
   * @see Drupal\Tests\BrowserTestBase
   */
  protected static $modules = [
    'utevent',
    'utevent_content_type_event',
    'utevent_view_listing_page',
    'utevent_vocabulary_location',
    'utevent_vocabulary_tags',
    'utevent_overrides',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->strictConfigSchema = NULL;
    parent::setUp();
    // Create a test media item.
    $this->testMediaImageId = $this->createTestMediaImage();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->testMediaImageFilename = $this->entityTypeManager->getStorage('media')
      ->load($this->testMediaImageId)
      ->get('field_utexas_media_image')
      ->entity
      ->getFileName();
    // Create a content editor user with all necessary permissions.
    $this->user = $this->drupalCreateUser();
    $this->user->addRole('utexas_content_editor');
    $this->user->save();
    UtexasPermissions::assignPermissions('editor', 'utexas_content_editor');
    UteventPermissions::assignPermissions('editor', 'utexas_content_editor');
  }

  /**
   * Test that Event content be created, viewed, edited, and deleted.
   */
  public function testUtevent() {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();
    // Enlarge the viewport so that everything is clickable.
    $this->getSession()->resizeWindow(1200, 3000);

    $this->drupalLogin($this->user);

    // Add an event location term.
    $this->drupalGet('/admin/structure/taxonomy/manage/utevent_location/add');
    $page->fillField('name[0][value]', 'Event location test');
    $page->pressButton('Save');

    // Add an event tag.
    $this->drupalGet('/admin/structure/taxonomy/manage/utevent_tags/add');
    $page->fillField('name[0][value]', 'Event tag test');
    $page->pressButton('Save');

    // Check past event listing response.
    $this->drupalGet('/events');
    $assert->pageTextContains('No upcoming events match the criteria.');

    // Navigate to node edit screen.
    $this->drupalGet('node/add/utevent_event');

    // Add field values.
    $next_year = date('Y') + 1;
    $page->fillField('title[0][value]', 'Test Event 1');
    $page->fillField('field_utevent_datetime[0][time_wrapper][value][date]', '07-31-' . $next_year);
    $page->fillField('field_utevent_datetime[0][time_wrapper][value][time]', '17:00:00');
    $page->fillField('field_utevent_datetime[0][time_wrapper][end_value][date]', '07-31-' . $next_year);
    $page->fillField('field_utevent_datetime[0][time_wrapper][end_value][time]', '18:00:00');

    // Access media library.
    $page->pressButton('edit-field-utevent-main-media-open-button');
    // Wait for media library to load.
    sleep(10);
    $this->assertTrue($assert->waitForText('Add or select media', 20000));
    // Select the test media item ("Image 1" with file name "test-image.png").
    $assert->elementExists('css', 'img[src*="' . $this->testMediaImageFilename . '"]')->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    // Wait for media library to close.
    $this->assertTrue($assert->waitForElementRemoved('css', '.ui-dialog-title'));

    $page->fillField('field_utevent_body[0][summary]', 'Summary text here');

    // Populate CKEditor field.
    $text = "<p>Pellentesque tristique senectus <strong>et netus</strong> et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p><ul><li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li><li>Aliquam tincidunt mauris eu risus.</li><li>Vestibulum auctor dapibus neque.</li></ul>";
    $this->fillCkeditorField('.form-item--field-utevent-body-0-value', $text);

    // Populate other fields on edit below.
    // Create the node.
    $page->pressButton('Save');

    $this->drupalLogout();
    $this->drupalGet('/events/test-event-1');
    $assert->elementTextEquals('css', 'h1', 'Test Event 1');
    $assert->elementTextEquals('css', '.field--name-field-utevent-status .field__item', 'Scheduled');
    $assert->elementTextEquals('css', '.field--name-field-utevent-datetime .field__item', 'July 31, ' . $next_year . ', 5 to 6 p.m. Add to calendar');
    $assert->responseNotContains('Location:');
    $assert->responseNotContains('Event tags:');
    $actual = $page->find('css', '.field--name-field-utevent-body .field__item')->getHTML();
    // Remove random-generated data-list-item-id values.
    $actual_clean = preg_replace('/\sdata-list-item-id="[A-Za-z0-9]*"/', '', $actual);
    $this->assertEquals($text, $actual_clean);
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.field--name-field-utevent-main-media'));

    // Make a change to the event and verify the node can be saved and
    // that the change is reflected in the output.
    $this->drupalLogin($this->user);
    $this->drupalGet('/node/1/edit');

    $page->fillField('field_utevent_display_media[value]', '0');
    $page->fillField('field_utevent_location[target_id]', 'Event location test');
    $page->fillField('field_utevent_tags[target_id]', 'Event tag test');
    $page->fillField('field_utevent_status', 'EventMovedOnline');
    $page->fillField('field_utevent_featured[value]', '1');

    $page->pressButton('Save');

    $this->drupalLogout();
    $this->drupalGet('/events/test-event-1');

    $assert->elementTextEquals('css', '.field--name-field-utevent-location .field__item', 'Event location test');
    $assert->elementTextEquals('css', '.field--name-field-utevent-tags .field__item', 'Event tag test');
    $assert->elementTextEquals('css', '.field--name-field-utevent-status .field__item', 'Moved online');

    // Verify the Add To Calendar button is operable.
    $page->pressButton('Add to calendar');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.add-to-calendar.modal'));
    $assert->elementTextEquals('css', '.addtocal__link.addtocal__google', 'Google');
    $assert->elementTextEquals('css', '.addtocal__link.addtocal__outlook', 'Outlook');
    $assert->elementTextEquals('css', '.addtocal__link.addtocal__ical', 'iCal');
    $page->pressButton('X');
    $assert->pageTextNotContains('Google');

    // Check past event listing response.
    $this->drupalGet('/past-events');
    $assert->pageTextContains('No past events match the criteria.');

    // Check event listing.
    $this->drupalGet('/events');
    $assert->linkExists('Test Event 1');
    $assert->elementTextEquals('css', '.views-field-field-utevent-datetime', 'Date and time: July 31, ' . $next_year . ', 5 to 6 p.m.');
    $assert->elementTextEquals('css', '.views-field-field-utevent-location', 'Location: Event location test');
    $assert->elementTextEquals('css', '.views-field-field-utevent-body', 'Summary text here');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.views-field-field-utevent-main-media'));

    // Confirm that an event node can be deleted from the system.
    $this->drupalLogin($this->user);
    $this->drupalGet('/node/1/delete');
    $this->assertTrue($assert->waitForText('Are you sure you want to delete the content item Test Event 1?'));
    $page->pressButton('Delete');
    $this->assertTrue($assert->waitForText('The Event Test Event 1 has been deleted.'));
  }

  /**
   * Set the value of a complex CKEditor enabled field.
   *
   * @param string $target
   *   The html name of the field that implements the editor.
   * @param string $value
   *   The value to enter into the field.
   */
  protected function fillCkeditorField($target, $value) {
    $assert_session = $this->assertSession();
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-editor'));
    $editor = "$target .ck-editor__editable";
    $session = $this->getSession();
    $ckeditor_javascript = "
    (function (){
        var domEditableElement = document.querySelector(\"$editor\");
        if (domEditableElement.ckeditorInstance) {
          const editorInstance = domEditableElement.ckeditorInstance;
          if (editorInstance) {
            editorInstance.setData(\"$value\");
          } else {
            throw new Exception('Could not get the editor instance!');
          }
        } else {
          throw new Exception('Could not find the element!');
        }
      }());";
    $session->executeScript($ckeditor_javascript);
  }

  /**
   * Creates a test image in Drupal and returns the media MID.
   *
   * @return string
   *   The MID.
   */
  protected function createTestMediaImage() {
    $images = $this->getTestFiles('image');
    // Create a File entity for the initial image. The zeroth element is a PNG.
    $file = File::create([
      'uri' => $images[0]->uri,
      'uid' => 1,
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    $file->save();
    $image_media = Media::create([
      'name' => 'Image 1',
      'bundle' => 'utexas_image',
      'uid' => '1',
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'status' => '1',
      'field_utexas_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Test Alt Text',
        'title' => 'Test Title Text',
      ],
    ]);
    $image_media->save();
    return $image_media->id();
  }

  /**
   * Check if two files are identical.
   *
   * @param string $a
   *   A valid path to a file.
   * @param string $b
   *   A valid path to a file.
   *
   * @return bool
   *   Whether or not the files are identical.
   */
  protected function filesAreEqual($a, $b) {
    // Check if filesize is different.
    if (filesize($a) !== filesize($b)) {
      return FALSE;
    }
    // Check if content is different.
    $ah = fopen($a, 'rb');
    $bh = fopen($b, 'rb');
    $result = TRUE;
    while (!feof($ah)) {
      if (fread($ah, 8192) != fread($bh, 8192)) {
        $result = FALSE;
        break;
      }
    }
    fclose($ah);
    fclose($bh);
    return $result;
  }

}
