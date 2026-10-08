<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Traits\TextFormatsTestTraits;

use Drupal\node\Entity\Node;

/**
 * Verifies text formats work as expected.
 */
trait TextFormatsTestTrait {

  /**
   * Verify allowed and disallowed attributes in Basic HTML.
   */
  public function verifyRestrictedHtmlAllowedTags() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\FlexHTMLTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $allowed_elements = [
      '<a href="https://google.com" hreflang="test" data-once="link">test</a>',
      '<em>test</em>',
      '<strong>test</strong>',
      '<cite>test</cite>',
      '<blockquote>test<p></p></blockquote>',
      '<code>test</code>',
      '<h2 id="test" tabindex="-1">test</h2>',
      '<h3 id="test" tabindex="-1">test</h3>',
      '<h4 id="test" tabindex="-1">test</h4>',
      '<h5 id="test" tabindex="-1">test</h5>',
      '<h6 id="test" tabindex="-1">test</h6>',
    ];

    $disallowed_elements = [
      '<iframe arbitrary-attribute="test" src="https://utexas.edu" title="Embedded content from utexas.edu">',
      '<a href="https://google.com" hreflang="test" arbitrary-attribute="test">test</a>',
      '<em class="test">test</em>',
      '<strong class="test">test</strong>',
      '<cite class="test">test</cite>',
      '<blockquote class="test">test<p></p></blockquote>',
      '<code class="test">test</code>',
      '<h1>test</h1>',
      '<h2 class="test" tabindex="-1">test</h2>',
      '<h3 class="test" tabindex="-1">test</h3>',
      '<h4 class="test" tabindex="-1">test</h4>',
      '<h5 class="test" tabindex="-1">test</h5>',
      '<h6 class="test" tabindex="-1">test</h6>',
      '<p class="test">test</p>',
      '<span>test</span>',
    ];
    // CRUD: CREATE.
    // Populate a node with the extant list of allowed attributes.
    // This doesn't need to be done via the UI because we'll test the UI later.
    $elements = array_merge($allowed_elements, $disallowed_elements);
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Source Test',
      'body' => [
        'value' => implode("", $elements),
        'format' => 'restricted_html',
      ],
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());
    foreach ($allowed_elements as $element) {
      $assert->responseContains($element);
    }
    foreach ($disallowed_elements as $element) {
      $assert->responseNotContains($element);
    }
  }

  /**
   * Verify allowed and disallowed attributes in Basic HTML.
   */
  public function verifyBasicHtmlAllowedTags() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\FlexHTMLTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $allowed_elements = [
      '<a href="https://google.com" hreflang="test" data-once="link">test</a>',
      '<em>test</em>',
      '<strong>test</strong>',
      '<cite>test</cite>',
      '<blockquote>test</blockquote>',
      '<code>test</code>',
      '<h2 id="test" tabindex="-1">test</h2>',
      '<h3 id="test" tabindex="-1">test</h3>',
      '<h4 id="test" tabindex="-1">test</h4>',
      '<h5 id="test" tabindex="-1">test</h5>',
      '<h6 id="test" tabindex="-1">test</h6>',
      '<p>test</p>',
      '<span>test</span>',
    ];

    $disallowed_elements = [
      '<iframe arbitrary-attribute="test" src="https://utexas.edu" title="Embedded content from utexas.edu">',
      '<a href="https://google.com" hreflang="test" arbitrary-attribute="test">test</a>',
      '<em class="test">test</em>',
      '<strong class="test">test</strong>',
      '<cite class="test">test</cite>',
      '<blockquote class="test">test</blockquote>',
      '<code class="test">test</code>',
      '<h1>test</h1>',
      '<h2 class="test" tabindex="-1">test</h2>',
      '<h3 class="test" tabindex="-1">test</h3>',
      '<h4 class="test" tabindex="-1">test</h4>',
      '<h5 class="test" tabindex="-1">test</h5>',
      '<h6 class="test" tabindex="-1">test</h6>',
      '<p class="test">test</p>',
      '<span class="test">test</span>',
    ];
    // CRUD: CREATE.
    // Populate a node with the extant list of allowed attributes.
    // This doesn't need to be done via the UI because we'll test the UI later.
    $elements = array_merge($allowed_elements, $disallowed_elements);
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Source Test',
      'body' => [
        'value' => implode("", $elements),
        'format' => 'basic_html',
      ],
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id() . '/edit');
    // Toggling between source mode will strip any disallowed tags.
    $page->pressButton('Source');
    sleep(4);
    $page->pressButton('Source');
    sleep(4);
    $page->pressButton('Save');
    foreach ($allowed_elements as $element) {
      $assert->responseContains($element);
    }
    foreach ($disallowed_elements as $element) {
      $assert->responseNotContains($element);
    }
  }

  /**
   * All tags and attributes are allowed in Full HTML.
   */
  public function verifyFullHtmlAllowedTags() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\FlexHTMLTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $elements = [
      '<iframe arbitrary-attribute="test" src="https://utexas.edu" title="Embedded content from utexas.edu">',
    ];

    // CRUD: CREATE.
    // Populate a node with the extant list of allowed attributes.
    // This doesn't need to be done via the UI because we'll test the UI later.
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Source Test',
      'body' => [
        'value' => implode("", $elements),
        'format' => 'full_html',
      ],
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());
    foreach ($elements as $element) {
      $assert->responseContains($element);
    }
  }

  /**
   * Source editing allows modifying/saving a list of allowed attributes.
   */
  public function verifyFlexHtmlSourceEditing() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\FlexHTMLTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $allowed_elements = [
      '<a href="https://google.com" hreflang="test" class="test" id="test" name="test" role="test" title="test" aria-controls="test" aria-haspopup="test" data-once="link" aria-label="test" aria-expanded="test" aria-selected="test" data-entity-substitution="test" data-entity-type="test" data-entity-uuid="test" data-toggle="test" data-slide="test" media="test" rel="test" target="test">test</a>',
      '<abbr title="test" class="test" id="test" role="test">test</abbr>',
      '<address class="test" id="test" role="test">test</address>',
      '<article class="test" id="test" role="test">test</article>',
      '<aside class="test" id="test" role="test">test</aside>',
      '<blockquote class="test" id="test" role="test">test</blockquote>',
      '<button type="test" class="test" id="test" role="test" aria-label="test" aria-expanded="test" aria-controls="test" aria-haspopup="test" data-toggle="test" data-target="test" data-dismiss="test" data-placement="test" data-container="test" title="test">test</button>',
      '<cite title="test" class="test" id="test" role="test">test</cite>',
      '<code class="test" id="test" role="test">test</code>',
      '<del class="test" id="test" role="test">test</del>',
      '<div role="test" class="test" id="test" aria-label="test" aria-labelledby="test" aria-hidden="test" data-ride="test" data-dismiss="test" data-toggle="test" data-parent="test" data-spy="test" data-offset="test" data-target="test" tabindex="test">',
      '<em class="test" id="test" role="test">test</em>',
      '<details class="test" id="test" role="test"><summary class="test" id="test" role="test">test</summary>test</details>',
      '<h1 class="test" id="test" role="test">test</h1>',
      '<h2 class="test" id="test" role="test" tabindex="-1">test</h2>',
      '<h3 class="test" id="test" role="test" tabindex="-1">test</h3>',
      '<h4 class="test" id="test" role="test" tabindex="-1">test</h4>',
      '<h5 class="test" id="test" role="test" tabindex="-1">test</h5>',
      '<h6 class="test" id="test" role="test" tabindex="-1">test</h6>',
      '<i class="test" id="test" role="test">test</i>',
      '<mark class="test" id="test" role="test">test</mark>',
      '<nav class="test" id="test" role="test" aria-label="test">test</nav>',
      '<p class="test" id="test" role="test">test</p>',
      '<footer class="test" id="test" role="test">test</footer>',
      '<header class="test" id="test" role="test">test</header>',
      '<pre class="test" id="test" role="test">test</pre>',
      '<s class="test" id="test" role="test">test</s>',
      '<section class="test" id="test" role="test">test</section>',
      '<small class="test" id="test" role="test">test</small>',
      '<span class="test" id="test" role="test" aria-hidden="test">test</span>',
      '<strike class="test" id="test" role="test">test</strike>',
      '<strong class="test" id="test" role="test">test</strong>',
      '<sub class="test" id="test" role="test">test</sub>',
      '<sup class="test" id="test" role="test">test</sup>',
      '<time class="test" id="test" role="test">test</time>',
      '<u class="test" id="test" role="test">test</u>',
    ];

    $disallowed_elements = [
      '<iframe arbitrary-attribute="test" src="https://utexas.edu" title="Embedded content from utexas.edu">',
      '<script>alert("Hi how are you");</script>',
      '<h2 arbitrary-attribute="test">test</h2>',
    ];
    $elements = array_merge($allowed_elements, $disallowed_elements);

    // CRUD: CREATE.
    // Populate a node with the extant list of allowed attributes.
    // This doesn't need to be done via the UI because we'll test the UI later.
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Source Test',
      'body' => [
        'value' => implode("", $elements),
        'format' => 'flex_html',
      ],
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id() . '/edit');
    // Toggling between source mode will strip any disallowed tags. This test
    // demonstrates that none of the tags we allow in
    // utexas_text_format_flex_html/config/install/editor.editor.flex_html.yml
    // are removed.
    $page->pressButton('Source');
    sleep(4);
    $page->pressButton('Source');
    sleep(4);
    $page->pressButton('Save');
    foreach ($allowed_elements as $element) {
      $assert->responseContains($element);
    }
    foreach ($disallowed_elements as $element) {
      $assert->responseNotContains($element);
    }
  }

  /**
   * Qualtrics Filter renders as expected.
   */
  public function verifyQualtricsFilterOutput() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\FlexHTMLTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    // Create node object with Qualtrics embed syntax.
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Qualtrics Test',
      'body' => [
        'value' => '[embed]https://utexas.qualtrics.com/SE/?SID=SV_af1Gk9JWK2khAEJ | height:500 | title:test[/embed]',
        'format' => 'flex_html',
      ],
    ]);
    $node->save();

    // CRUD: READ.
    $this->drupalGet('node/' . $node->id());
    $expected_src = 'https://utexas.qualtrics.com/SE/?SID=SV_af1Gk9JWK2khAEJ';
    $assert->elementAttributeContains('css', '.region-content iframe', 'src', $expected_src);

    // CRUD: DELETE.
    $this->removeNodes([$node->id()]);
  }

}
