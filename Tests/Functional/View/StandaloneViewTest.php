<?php
namespace TYPO3\Fluid\Tests\Functional\View;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Mvc\ActionRequest;

/**
 * Testcase for Standalone View
 */
class StandaloneViewTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var string
	 */
	protected $standaloneViewNonce = '42';

	/**
	 * Every testcase should run *twice*. First, it is run in *uncached* way, second,
	 * it is run *cached*. To make sure that the first run is always uncached, the
	 * $standaloneViewNonce is initialized to some random value which is used inside
	 * an overridden version of StandaloneView::createIdentifierForFile.
	 */
	public function runBare() {
		$this->standaloneViewNonce = uniqid();
		parent::runBare();
		$numberOfAssertions = $this->getNumAssertions();
		parent::runBare();
		$this->addToAssertionCount($numberOfAssertions);
	}

	/**
	 * @test
	 */
	public function inlineTemplateIsEvaluatedCorrectly() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->assign('foo', 'bar');
		$standaloneView->setTemplateSource('This is my cool {foo} template!');

		$expected = 'This is my cool bar template!';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function renderSectionIsEvaluatedCorrectly() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->assign('foo', 'bar');
		$standaloneView->setTemplateSource('Around stuff... <f:section name="innerSection">test {foo}</f:section> after it');

		$expected = 'test bar';
		$actual = $standaloneView->renderSection('innerSection');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\View\Exception\InvalidTemplateResourceException
	 */
	public function renderThrowsExceptionIfNeitherTemplateSourceNorTemplatePathAndFilenameAreSpecified() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->render();
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\View\Exception\InvalidTemplateResourceException
	 */
	public function renderThrowsExceptionSpecifiedTemplatePathAndFilenameDoesNotExist() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/NonExistingTemplate.txt');
		$standaloneView->render();
	}

	/**
	 * @test
	 */
	public function templatePathAndFilenameIsLoaded() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->assign('name', 'Karsten');
		$standaloneView->assign('name', 'Robert');
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplate.txt');

		$expected = 'This is a test template. Hello Robert.';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function variablesAreEscapedByDefault() {
		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView(NULL, $this->standaloneViewNonce);
		$standaloneView->assign('name', 'Sebastian <script>alert("dangerous");</script>');
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplate.txt');

		$expected = 'This is a test template. Hello Sebastian &lt;script&gt;alert(&quot;dangerous&quot;);&lt;/script&gt;.';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function variablesAreEscapedIfRequestFormatIsHtml() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();
		$actionRequest->setFormat('html');

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->assign('name', 'Sebastian <script>alert("dangerous");</script>');
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplate.txt');

		$expected = 'This is a test template. Hello Sebastian &lt;script&gt;alert(&quot;dangerous&quot;);&lt;/script&gt;.';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function variablesAreNotEscapedIfRequestFormatIsNotHtml() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();
		$actionRequest->setFormat('txt');

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->assign('name', 'Sebastian <script>alert("dangerous");</script>');
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplate.txt');

		$expected = 'This is a test template. Hello Sebastian <script>alert("dangerous");</script>.';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function partialWithDefaultLocationIsUsedIfNoPartialPathIsSetExplicitely() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();
		$actionRequest->setFormat('txt');

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplateWithPartial.txt');

		$expected = 'This is a test template. Hello Robert.';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function explicitPartialPathIsUsed() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();
		$actionRequest->setFormat('txt');

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplateWithPartial.txt');
		$standaloneView->setPartialRootPath(__DIR__ . '/Fixtures/SpecialPartialsDirectory');

		$expected = 'This is a test template. Hello Karsten.';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function layoutWithDefaultLocationIsUsedIfNoLayoutPathIsSetExplicitely() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();
		$actionRequest->setFormat('txt');

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplateWithLayout.txt');

		$expected = 'Hey HEY HO';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function explicitLayoutPathIsUsed() {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();
		$actionRequest->setFormat('txt');
		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, $this->standaloneViewNonce);
		$standaloneView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TestTemplateWithLayout.txt');
		$standaloneView->setLayoutRootPath(__DIR__ . '/Fixtures/SpecialLayouts');

		$expected = 'Hey -- overridden -- HEY HO';
		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}

}
?>