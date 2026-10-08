<?php

namespace Drupal\Tests\utnews\FunctionalJavascript;

use Drupal\Core\Language\Language;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\utexas\Permissions as UtexasPermissions;
use Drupal\utnews\Permissions as UtnewsPermissions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test all aspects of News CRUD functionality.
 */
#[RunTestsInSeparateProcesses]
#[Group('utexas')]
class BasicUtnewsTest extends WebDriverTestBase {

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
   * A user with permissions to administer news.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Tests must specify what theme will be used.
   *
   * @var string
   */
  protected $defaultTheme = 'speedway';

  /**
   * Modules to enable.
   *
   * @var array
   *
   * @see Drupal\Tests\BrowserTestBase
   */
  protected static $modules = [
    'utnews',
    'utnews_content_type_news',
    'utnews_view_listing_page',
    'utnews_vocabulary_authors',
    'utnews_vocabulary_categories',
    'utnews_vocabulary_tags',
    'utnews_overrides',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->strictConfigSchema = NULL;
    parent::setUp();
    // Create a test media item.
    $media = $this->createTestMediaImage();
    $this->testMediaImageId = $media['id'];
    $this->testMediaImageFilename = $media['filename'];
    // Create a content editor user with all necessary permissions.
    $this->user = $this->drupalCreateUser();
    $this->user->addRole('utexas_content_editor');
    $this->user->save();
    UtexasPermissions::assignPermissions('editor', 'utexas_content_editor');
    UtnewsPermissions::assignPermissions('editor', 'utexas_content_editor');

    // Programmatically generate news categories & authors.
    $news_categories = [
      'General',
      'Opinion',
      'Press Releases',
    ];
    $weight = 0;
    foreach ($news_categories as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utnews_categories',
        'weight' => $weight,
      ])->save();
      $weight++;
    }
    $news_author = ['Demo Author 1', 'Demo Author 2'];
    $weight = 0;
    foreach ($news_author as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utnews_authors',
        'weight' => $weight,
        'field_utnews_author_image' => $this->testMediaImageId,
      ])->save();
      $weight++;
    }
    $news_tags = [
      'Demo Tag 1',
      'Demo Tag 2',
    ];
    $weight = 0;
    foreach ($news_tags as $term) {
      Term::create([
        'name' => $term,
        'vid' => 'utnews_tags',
        'weight' => $weight,
      ])->save();
      $weight++;
    }
    drupal_flush_all_caches();
  }

  /**
   * Test that News content be created, viewed, edited, and deleted.
   */
  public function testUtnews() {
    // Enlarge the viewport so that everything is clickable.
    $this->getSession()->resizeWindow(1200, 3000);
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    // Sign in as our user with the necessary permissions.
    $this->drupalLogin($this->user);

    // Navigate to node edit screen.
    $this->drupalGet('node/add/utnews_news');

    // Add field values.
    $page->fillField('title[0][value]', 'Test News 1');
    $page->fillField('field_utnews_publication_date[0][value][date]', '07-31-2023');

    // Access media library.
    $page->pressButton('edit-field-utnews-main-media-open-button');
    // Wait for media library to load.
    sleep(10);
    $this->assertTrue($assert->waitForText('Add or select media', 20000));
    // Select the test media item ("Image 1" with file name "test-image.png").
    $assert->elementExists('css', 'img[src*="' . $this->testMediaImageFilename . '"]')->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    // Wait for media library to close.
    $this->assertTrue($assert->waitForElementRemoved('css', '.ui-dialog-title'));

    $page->fillField('field_utnews_body[0][summary]', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');

    // Populate CKEditor field.
    $text = "<p>Pellentesque tristique senectus <strong>et netus</strong> et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p><ul><li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li><li>Aliquam tincidunt mauris eu risus.</li><li>Vestibulum auctor dapibus neque.</li></ul>";
    $this->fillCkeditorField('.form-item--field-utnews-body-0-value', $text);

    sleep(1);
    // Add a demo tag, but not category or author.
    $page->fillField('field_utnews_news_tags[target_id]', 'Demo Tag 1');
    // Create the node.
    $page->pressButton('Save');

    // View as an anonymous user.
    $this->drupalLogout();
    $this->drupalGet('/news');
    // The Tags select dropdown displays because an item has been tagged.
    $assert->elementTextEquals('css', 'select[name="tags"] option:nth-of-type(2)', 'Demo Tag 1');
    // The 'Category' dropdown does not render because no categories are tagged.
    $assert->elementNotExists('css', 'select[name="category"]');
    // The 'Author' dropdown does not render because no authors are tagged.
    $assert->elementNotExists('css', 'select[name="author"]');
    // The listing view displays "Search" button when at least one dropdown is
    // present, but the "Reset" button is omitted.
    $assert->elementExists('css', '[data-drupal-selector="edit-submit-utnews-listing-page"]');
    $assert->elementNotExists('css', '[data-drupal-selector="edit-reset"]');
    $page->fillField('tags', '6');
    $page->pressButton('edit-submit-utnews-listing-page');
    // After a filter is performed, the "Reset" button is available.
    $assert->elementExists('css', '[data-drupal-selector="edit-reset"]');

    $this->drupalLogin($this->user);
    $this->drupalGet('/node/1/edit');
    // Add news category and author.
    $page->checkField('field_utnews_news_categories[3]');
    $page->fillField('field_utnews_article_author', '4');
    $page->pressButton('Save');

    // View as an anonymous user.
    $this->drupalLogout();
    $this->drupalGet('/news/test-news-1');

    $assert->elementTextEquals('css', 'h1', 'Test News 1');
    $assert->elementTextEquals('css', '.utnews__author-wrapper', 'By Demo Author 1');
    $assert->elementTextEquals('css', '.utnews__published-wrapper', 'July 31, 2023');
    $assert->elementTextEquals('css', '.utnews__categories-wrapper', 'News category Press Releases');
    $assert->elementTextEquals('css', '.utnews__tags-wrapper', 'News tags Demo Tag 1');
    $actual = $page->find('css', '.field--name-field-utnews-body')->getHTML();
    // Remove random-generated data-list-item-id values.
    $actual_clean = preg_replace('/\sdata-list-item-id="[A-Za-z0-9]*"/', '', $actual);
    $this->assertEquals($text, $actual_clean);
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.field--name-field-utexas-media-image'), 'The news node should display an image.');
    $assert->elementTextEquals('css', '.utnews__author-information-wrapper h3', 'Demo Author 1');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utnews__author-information-wrapper .field--name-field-utexas-media-image'));
    // Sidebar links on individual news articles go to the filter result.
    $this->clickLink('Demo Tag 1');
    $assert->addressEquals('/news?tags=6');

    // Set the news article to an external link and save the node.
    $this->drupalLogin($this->user);
    $this->drupalGet('/node/1/edit');
    $page->fillField('field_utnews_external_link[0][uri]', 'https://news.utexas.edu');
    $page->fillField('field_utnews_external_link[0][options][attributes][class]', 'ut-cta-link--external');
    // Save the new changes.
    $page->pressButton('Save');
    $this->drupalLogout();

    // Perform a test of /news (the listing page),
    // Confirming that the external link icon is present.
    $this->drupalGet('/news');
    $assert->elementTextEquals('css', 'h1', 'News');
    $assert->elementTextEquals('css', '.utnews__content-wrapper h3', 'Test News 1');
    $this->assertEquals('<a href="https://news.utexas.edu" class="ut-cta-link--external" data-once="link" aria-label="Test News 1; external link">Test News 1</a>', $page->find('css', '.utnews__content-wrapper h3')->getHTML(), 'An news article with an external link links to the external link.');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utnews__content-wrapper .field--name-field-utnews-main-media'), 'The news teaser should display an image.');
    $assert->linkByHrefExists('https://news.utexas.edu', 0, 'The news title links to an external URL.');
    $assert->elementTextEquals('css', '.field--name-field-utnews-publication-date', 'July 31, 2023');
    $assert->elementTextEquals('css', '.field--name-field-utnews-body', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
    $assert->pageTextContains('Filter by Category');
    $assert->pageTextContains('Filter by Tag');
    $assert->pageTextContains('Filter by Author');
    // Demo Tag 1 is listed as a filter option since an article tags it.
    $assert->elementTextEquals('css', 'select[name="tags"] option:nth-of-type(2)', 'Demo Tag 1');
    // Demo Tag 2 is NOT listed, because no articles tag it.
    $assert->elementNotExists('css', 'select[name="tags"] option:nth-of-type(3)');
    // Category dropdown is present.
    $assert->elementExists('css', 'select[name="category"]');
    // Author dropdown is present.
    $assert->elementExists('css', 'select[name="author"]');

    // Confirm that a News node can be deleted from the system via user
    // actions.
    // Sign in as our user with the necessary permissions.
    $this->drupalLogin($this->user);
    $this->drupalGet('/node/1/delete');
    $this->assertTrue($assert->waitForText('Are you sure you want to delete the content item Test News 1?'));
    $page->pressButton('Delete');
    $this->assertTrue($assert->waitForText('The News article Test News 1 has been deleted.'));

    $this->drupalLogout();
    $this->drupalGet('/news');
    // Since no published articles are associated with author, tag, or category,
    // no exposed filters display, without requiring a cache rebuild.
    $assert->elementNotExists('css', 'select[name="category"]');
    $assert->elementNotExists('css', 'select[name="author"]');
    $assert->elementNotExists('css', 'select[name="tags"]');

    // If there are more than 100 items in a dropdown, it is capped at 100.
    $weight = 3;
    $tids = [];
    foreach (range(101, 300) as $number) {
      $term = Term::create([
        'name' => "Demo Tag $number",
        'vid' => 'utnews_tags',
        'weight' => $weight,
      ]);
      $term->save();
      $tids[] = $term->id();
      $weight++;
    }
    $node = Node::create(['type' => 'utnews_news']);
    $node->set('title', 'A Very Silly News Node');
    $node->set('uid', '1');
    $node->set('field_utnews_news_tags', $tids);
    $body = [
      'value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
      'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'format' => 'flex_html',
    ];
    $node->set('field_utnews_body', $body);
    $node->status = 1;
    $node->enforceIsNew();
    $node->save();

    $this->drupalGet('/news');
    $assert->elementExists('css', 'select[name="tags"] option:nth-of-type(100)');
    $assert->elementNotExists('css', 'select[name="tags"] option:nth-of-type(101)');
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
    $media = Media::create([
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
    $media->save();
    return [
      'id' => $media->id(),
      'filename' => $file->getFilename(),
    ];
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
