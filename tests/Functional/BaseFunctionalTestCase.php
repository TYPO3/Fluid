<?php
namespace NamelessCoder\Fluid\Tests\Functional;

use NamelessCoder\Fluid\Core\Cache\FluidCacheInterface;
use NamelessCoder\Fluid\Core\Rendering\RenderingContext;
use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Core\Variables\VariableProviderInterface;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\UnitTestCase;
use NamelessCoder\Fluid\View\TemplatePaths;
use NamelessCoder\Fluid\View\TemplateView;
use NamelessCoder\Fluid\View\ViewInterface;

/**
 * Class BaseFunctionalTestCase
 */
abstract class BaseFunctionalTestCase extends UnitTestCase {

	/**
	 * If your test case requires a cache, override this
	 * method and return an instance.
	 *
	 * @return FluidCacheInterface
	 */
	protected function getCache() {
		return NULL;
	}

	/**
	 * If your test case requires a custom VariableProvider
	 * implementation, return the instance from this method.
	 *
	 * @return VariableProviderInterface
	 */
	protected function getVariableProvider() {
		return new StandardVariableProvider();
	}

	/**
	 * If your test case requires a custom RenderingContext,
	 * return the instance from this method.
	 *
	 * @return RenderingContext
	 */
	protected function getRenderingContext() {
		return new RenderingContext();
	}

	/**
	 * If your test case requires a custom ViewHelperResolver,
	 * return the instance from this method.
	 *
	 * @return ViewHelperResolver
	 */
	protected function getViewHelperResolver() {
		return new ViewHelperResolver();
	}

	/**
	 * If your test case requires a custom TemplatePaths
	 * implementation, return the instance from this method.
	 *
	 * @return TemplatePaths
	 */
	protected function getTemplatePaths() {
		return new TemplatePaths();
	}

	/**
	 * If your test case requires a custom View instance
	 * return the instance from this method.
	 *
	 * @return ViewInterface
	 */
	protected function getView($withCache = FALSE) {
		$paths = $this->getTemplatePaths();
		$context = $this->getRenderingContext();
		$resolver = $this->getViewHelperResolver();
		if (!$withCache) {
			$view = new TemplateView($paths, $context);
		} else {
			$cache = $this->getCache();
			$view = new TemplateView($paths, $context, $cache);
		}

		$view->setViewHelperResolver($resolver);
		return $view;
	}

	/**
	 * Data sets used by the standard function test in test case.
	 *
	 * Override this method and return an array of arrays
	 * which each must contain, in order:
	 *
	 * array(
	 *     'template {code} piece', // the template code as string; or an (open) stream
	 *     array('code' => 'test'), // the variables that will be assigned
	 *     array('template test', 'test piece'), // list of values that MUST all be present
	 *     array('negative test', 'test bad') // list of values that MUST NOT be present
	 * )
	 *
	 * Name your sets in order to improve the error reporting:
	 *
	 * return array(
	 *     'Outputs foobar and baz' => array(
	 *          // ...
	 *     ),
	 *     'Outputs not foobar' => array(
	 *          // ...
	 *     ),
	 *     'Outputs baz but not foobar' => array(
	 *          // ...
	 *     )
	 * );
	 *
	 * This will clearly report *which* of the sets caused failure
	 * when you run the test case.
	 *
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array();
	}

	/**
	 * Perform a standard test on the source or stream provided,
	 * rendering it with $variables assigned and checking the
	 * output for presense of $expected values and confirming
	 * that none of the $notExpected values are present.
	 *
	 * @param string|resource $source
	 * @param array $variables
	 * @param array $expected
	 * @param array $notExpected
	 * @param boolean $withCache
	 * @test
	 * @dataProvider getTemplateCodeFixturesAndExpectations
	 */
	public function testTemplateCodeFixture($source, array $variables, array $expected, array $notExpected, $withCache = FALSE) {
		$view = $this->getView(FALSE);
		$view->getTemplatePaths()->setTemplateSource($source);
		$view->assignMultiple($variables);
		$output = $view->render();
		$this->assertNotEquals($view->getTemplatePaths()->getTemplateSource(), $output, 'Input and output were the same');
		if (empty($expected) && empty($notExpected)) {
			$this->fail('Test performs no assertions!');
		}
		foreach ($expected as $expectedValue) {
			$this->assertContains($expectedValue, $output);
		}
		foreach ($notExpected as $notExpectedValue) {
			$this->assertNotContains($notExpectedValue, $output);
		}
	}

	/**
	 * Perform a standard test on the source or stream provided,
	 * rendering it with $variables assigned and checking the
	 * output for presense of $expected values and confirming
	 * that none of the $notExpected values are present.
	 *
	 * Same as testTemplateCodeFixture() but includes a cache
	 * in the tests. Silently skipped if the test case does not
	 * return a valid cache.
	 *
	 * @param string|resource $sourceOrStream
	 * @param array $variables
	 * @param array $expected
	 * @param array $notExpected
	 * @test
	 * @dataProvider getTemplateCodeFixturesAndExpectations
	 */
	public function testTemplateCodeFixtureWithCache($sourceOrStream, array $variables, array $expected, array $notExpected) {
		if ($this->getCache()) {
			$this->testTemplateCodeFixture($sourceOrStream, $variables, $expected, $notExpected, TRUE);
		}
	}

}
