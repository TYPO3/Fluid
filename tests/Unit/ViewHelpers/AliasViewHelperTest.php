<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\ViewHelpers\AliasViewHelper;

/**
 * Testcase for AliasViewHelper
 */
class AliasViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock(AliasViewHelper::class, array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('map', 'array', $this->anything());
		$instance->expects($this->at(1))->method('registerArgument')->with('src', 'mixed', $this->anything());
		$instance->expects($this->at(2))->method('registerArgument')->with('as', 'string', $this->anything());
		$instance->expects($this->at(3))->method('registerArgument')->with('type', 'string', $this->anything());
		$instance->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderAddsSingleValueToTemplateVariableContainerAndRemovesItAfterRendering() {
		$viewHelper = new AliasViewHelper();
		$viewHelper->setRenderChildrenClosure(function() { return 'foo'; });
		$arguments = array('map' => array('someAlias' => 'someValue'));
		$mockViewHelperNode = $this->getMock(
			ViewHelperNode::class,
			array('evaluateChildNodes'), array(new RenderingContextFixture(), 'f', 'alias', $arguments, new ParsingState())
		);

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($mockViewHelperNode);
		$viewHelper->setArguments($arguments);
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderAddsMultipleValuesToTemplateVariableContainerAndRemovesThemAfterRendering() {
		$viewHelper = new AliasViewHelper();
		$arguments = array('map' => array('someAlias' => 'someValue', 'someOtherAlias' => 'someOtherValue'));
		$mockViewHelperNode = $this->getMock(
			ViewHelperNode::class,
			array('evaluateChildNodes'), array(new RenderingContextFixture(), 'f', 'alias', $arguments, new ParsingState())
		);

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($mockViewHelperNode);
		$viewHelper->setArguments($arguments);
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function testCreatesVariableProvider() {
		$viewHelper = new AliasViewHelper();
		$arguments = array('map' => array(), 'src' => '{"foo": "bar"}', 'as' => 'baz', 'type' => 'json');
		$context = new RenderingContextFixture();
		$provider = $this->getMock(StandardVariableProvider::class, array('add', 'remove'));
		$provider->expects($this->once())->method('add')->with('baz', $this->anything());
		$provider->expects($this->once())->method('remove')->with('baz');
		$context->setVariableProvider($provider);
		$mockViewHelperNode = $this->getMock(
			ViewHelperNode::class,
			array('dummy'), array($context, 'f', 'alias', $arguments, new ParsingState())
		);

		$viewHelper->setRenderingContext($context);
		$viewHelper->setViewHelperNode($mockViewHelperNode);
		$viewHelper->setArguments($arguments);
		$viewHelper->render();
	}

	/**
	 * @param array $arguments
	 * @test
	 * @dataProvider getExceptionTestValues
	 */
	public function testThrowsExceptionOnInvalidArguments(array $arguments) {
		$this->setExpectedException(ParserException::class);
		$viewHelper = new AliasViewHelper();
		$arguments = array('map' => array());
		$mockViewHelperNode = $this->getMock(
			ViewHelperNode::class,
			array('evaluateChildNodes'), array(new RenderingContextFixture(), 'f', 'alias', $arguments, new ParsingState())
		);

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($mockViewHelperNode);

		$viewHelper->setArguments($arguments);
		$viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * @return array
	 */
	public function getExceptionTestValues() {
		return array(
			array(array('map' => array(), 'src' => NULL, 'as' => NULL)),
			array(array('map' => array('foo' => 'bar'), 'src' => 'foo', 'as' => NULL)),
			array(array('map' => array('foo' => 'bar'), 'src' => 'foo', 'as' => 'baz')),
			array(array('map' => array(), 'src' => 'foo', 'as' => NULL)),
			array(array('map' => array(), 'src' => 'foo', 'as' => 'bar', 'type' => NULL)),
		);
	}
}
