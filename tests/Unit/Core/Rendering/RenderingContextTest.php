<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;

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
		$templateVariableContainer = $this->getMock('TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider');
		$this->renderingContext->setVariableProvider($templateVariableContainer);
		$this->assertSame($this->renderingContext->getVariableProvider(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
	}

	/**
	 * @test
	 */
	public function viewHelperVariableContainerCanBeReadCorrectly() {
		$viewHelperVariableContainer = $this->getMock('TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		$this->assertSame($viewHelperVariableContainer, $this->renderingContext->getViewHelperVariableContainer());
	}
}
