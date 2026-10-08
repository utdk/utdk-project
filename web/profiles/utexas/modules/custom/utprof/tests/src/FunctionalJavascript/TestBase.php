<?php

declare(strict_types=1);

namespace Drupal\Tests\utprof\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utprof\Traits\EntityTestTrait;
use Drupal\Tests\utprof\Traits\ProfileTestTrait;
use Drupal\Tests\utprof\Traits\UserTestTrait;
use Drupal\Tests\utprof\Traits\ViewModeTests\ProfileViewModeBasicTrait;
use Drupal\Tests\utprof\Traits\ViewModeTests\ProfileViewModeDefaultTrait;
use Drupal\Tests\utprof\Traits\ViewModeTests\ProfileViewModeFullTrait;
use Drupal\Tests\utprof\Traits\ViewModeTests\ProfileViewModeNameOnlyTrait;
use Drupal\Tests\utprof\Traits\ViewModeTests\ProfileViewModeProminentTrait;
use Drupal\Tests\utprof\Traits\ViewModeTests\ProfileViewModeTeaserTrait;

/**
 * Base class for Functional Javascript tests.
 */
abstract class TestBase extends WebDriverTestBase {

  use TestFileCreationTrait;
  use EntityTestTrait;
  use UserTestTrait;
  use ProfileTestTrait;
  use ProfileViewModeBasicTrait;
  use ProfileViewModeDefaultTrait;
  use ProfileViewModeFullTrait;
  use ProfileViewModeNameOnlyTrait;
  use ProfileViewModeProminentTrait;
  use ProfileViewModeTeaserTrait;

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
   * The entity manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * Modules to enable.
   *
   * @var array
   *
   * @see Drupal\Tests\BrowserTestBase
   */
  protected static $modules = [
    'utprof',
    'utprof_demo_content',
    'utprof_content_type_profile',
    'utprof_block_type_profile_listing',
    'utprof_vocabulary_groups',
    'utprof_vocabulary_tags',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->strictConfigSchema = NULL;
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->renderer = $this->container->get('renderer');
    $this->testMediaImageId = $this->createTestMediaImage();
    $this->testMediaImageFilename = $this->entityTypeManager->getStorage('media')
      ->load($this->testMediaImageId)
      ->get('field_utexas_media_image')
      ->entity
      ->getFileName();
    $this->drupalLogin($this->rootUser);
  }

}
