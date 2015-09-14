<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;
use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use NamelessCoder\Fluid\Tests\UnitTestCase;
use NamelessCoder\Fluid\Core\Rendering\RenderingContext;

/**
 * Testcase for ParsingState
 *
 */
class RenderingContextTest extends UnitTestCase {

	/**
	 * @var RenderingContextInterface
	 */
	protected $renderingContext;

	public function setUp() {
		$this->renderingContext = new RenderingContext();
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 * @dataProvider getPropertyNameTestValues
	 */
	public function testGetter($property, $value) {
		$subject = $this->getAccessibleMock('NamelessCoder\\Fluid\\Core\\Rendering\\RenderingContext', array('dummy'));
		$subject->_set($property, $value);
		$getter = 'get' . ucfirst($property);
		$this->assertSame($value, $subject->$getter());
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 * @dataProvider getPropertyNameTestValues
	 */
	public function testSetter($property, $value) {
		$subject = new RenderingContext();
		$setter = 'set' . ucfirst($property);
		$subject->$setter($value);
		$this->assertAttributeSame($value, $property, $subject);
	}

	/**
	 * @return array
	 */
	public function getPropertyNameTestValues() {
		return array(
			array('variableProvider', new StandardVariableProvider(array('foo' => 'bar'))),
			array('viewHelperResolver', new ViewHelperResolver()),
			array('controllerName', 'foobar-controllerName'),
			array('controllerAction', 'foobar-controllerAction'),
		);
	}

	/**
	 * @test
	 */
	public function templateVariableContainerCanBeReadCorrectly() {
		$templateVariableContainer = $this->getMock('NamelessCoder\Fluid\Core\Variables\StandardVariableProvider');
		$this->renderingContext->setVariableProvider($templateVariableContainer);
		$this->assertSame($this->renderingContext->getVariableProvider(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
	}

	/**
	 * @test
	 */
	public function viewHelperVariableContainerCanBeReadCorrectly() {
		$viewHelperVariableContainer = $this->getMock('NamelessCoder\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		$this->assertSame($viewHelperVariableContainer, $this->renderingContext->getViewHelperVariableContainer());
	}
}
