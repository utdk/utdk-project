<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\node\Entity\Node;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies Bootstrap Javascript works as intended (#3108).
 */
#[RunTestsInSeparateProcesses]
class BootstrapFrameworkTest extends FunctionalJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testContentEditorUser);
  }

  /**
   * Bootstrap Framework tests.
   */
  public function testBootstrapFramework() {
    $assert = $this->assertSession();
    $markup = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  Launch demo modal
</button>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Home</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Profile</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</button>
  </li>
</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">...</div>
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">Profile tab content</div>
  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
</div>

<button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Tooltip text">
  Tooltip on top
</button>

<div class="dropdown">
  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    Dropdown button
  </button>
  <ul class="dropdown-menu">
    <li><a class="dropdown-item" href="#">Action</a></li>
    <li><a class="dropdown-item" href="#">Another action</a></li>
    <li><a class="dropdown-item" href="#">Something else here</a></li>
  </ul>
</div>

<p class="d-inline-flex gap-1">
  <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
    Link with href
  </a>
  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
    Button with data-bs-target
  </button>
</p>
<div class="collapse" id="collapseExample">
  <div class="card card-body">
    Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
  </div>
</div>
';
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Test Bootstrap Framework',
    ]);
    $node->set('body', [
      'value' => $markup,
      'format' => 'full_html',
    ]);
    $id = $node->save();
    $this->drupalGet('/node/' . $id);
    $page = $this->getSession()->getPage();

    $assert->PageTextContains('Profile');
    $assert->PageTextNotContains('Profile tab content');
    $page->pressButton('Profile');
    $this->assertTrue($assert->waitForText('Profile tab content'));

    $assert->PageTextContains('Dropdown button');
    $assert->PageTextNotContains('Something else here');
    $page->pressButton('Dropdown button');
    $this->assertTrue($assert->waitForText('Something else here'));

    $assert->PageTextContains('Tooltip on top');
    $this->assertNull($assert->waitForElement('css', '.bs-tooltip-auto'));
    $page->pressButton('Tooltip on top');
    $this->assertNotNull($assert->waitForElement('css', '.bs-tooltip-auto'));

    $assert->PageTextContains('Button with data-bs-target');
    $assert->PageTextNotContains('Some placeholder content for the collapse component');
    $page->pressButton('Button with data-bs-target');
    $this->assertTrue($assert->waitForText('Some placeholder content for the collapse component'));

    $assert->PageTextContains('Launch demo modal');
    $assert->PageTextNotContains('Modal title');
    $page->pressButton('Launch demo modal');
    $this->assertTrue($assert->waitForText('Modal title'));
  }

}
