<?php

namespace Drupal\Tests\utexas_qualtrics_filter\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\filter\Plugin\Filter\FilterHtml;
use Drupal\utexas_qualtrics_filter\Plugin\Filter\FilterQualtrics;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @coversDefaultClass \Drupal\utexas_qualtrics_filter\Plugin\Filter\FilterQualtrics
 */
#[Group('filter')]
class FilterQualtricsTest extends UnitTestCase {

  /**
   * The filter class.
   *
   * @var Drupal\filter\Plugin\Filter\FilterHtml
   */
  protected $filter;

  /**
   * The Qualtrics filter class.
   *
   * @var \Drupal\utexas_qualtrics_filter\Plugin\Filter\FilterQualtrics
   */
  protected $filterQualtrics;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $configuration['settings'] = [
      'allowed_html' => '<a href> <p> <em> <strong> <cite> <blockquote> <code class="pretty boring align-*"> <ul alpaca-*="wooly-* strong"> <ol llama-*> <li> <dl> <dt> <dd> <br> <h3 id> <iframe>',
      'filter_html_help' => 1,
      'filter_html_nofollow' => 0,
    ];
    $this->filter = new FilterHtml($configuration, 'filter_html', ['provider' => 'test']);
    $this->filter->setStringTranslation($this->getStringTranslationStub());

    $qualtrics_settings['settings'] = [
      'qualtrics_css' => 0,
    ];
    // See Drupal\Core\Plugin\PluginBase.
    $this->filterQualtrics = new FilterQualtrics([], 'filter_qualtrics', ['provider' => 'test']);
  }

  /**
   * @covers ::utexas_qualtrics_filter
   *
   *
   * @param string $html
   *   Input HTML.
   * @param string $expected
   *   The expected output string.
   */
  #[DataProvider('providerFilterAttributes')]
  public function testfilterAttributes($html, $expected) {
    $html_filter = $this->filter->filterAttributes($html);
    $result = $this->filterQualtrics->convertToIframe($html_filter);
    $result = preg_replace('/\s+/', ' ', trim($result));
    $this->assertSame($expected, $result);
  }

  /**
   * Provides data for testfilterAttributes.
   *
   * @return array
   *   An array of test data.
   */
  public static function providerFilterAttributes() {
    return [
      [
        '[embed]https://utexas.qualtrics.com/jfe/form/SV_5v9vZ8R3joHCOgt | height:400 | title:hola[/embed]',
        '<iframe src="https://utexas.qualtrics.com/jfe/form/SV_5v9vZ8R3joHCOgt" width="100%" scrolling="auto" name="Qualtrics" align="center" height="400" frameborder="no" title="hola" class="qualtrics-form" ></iframe>',
      ],
      [
        '[embed]https://utexas.qualtrics.com/jfe/form/SV_5v9vZ8R3joHCOgt[/embed]',
        '<iframe src="https://utexas.qualtrics.com/jfe/form/SV_5v9vZ8R3joHCOgt" width="100%" scrolling="auto" name="Qualtrics" align="center" height="500" frameborder="no" title="Qualtrics Form" class="qualtrics-form" ></iframe>',
      ],
      [
        '[embed]https://google.com/helloworld[/embed]',
        '[embed]https://google.com/helloworld[/embed]',
      ],
      [
        '<p>No embed content</p>',
        '<p>No embed content</p>',
      ],
    ];
  }

}
