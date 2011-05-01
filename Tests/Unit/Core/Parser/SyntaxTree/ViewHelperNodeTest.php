<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../Fixtures/ChildNodeAccessFacetViewHelper.php');
require_once(__DIR__ . '/../../Fixtures/TestViewHelper.php');

/**
 * Testcase for [insert classname here]
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ViewHelperNodeTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * Rendering Context
	 * @var F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * Object factory mock
	 * @var F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * Template Variable Container
	 * @var F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 *
	 * @var F3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;

	/**
	 * @var F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * Setup fixture
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->renderingContext = new \F3\Fluid\Core\Rendering\RenderingContext();

		$this->mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
		$this->renderingContext->injectObjectManager($this->mockObjectManager);

		$this->templateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->renderingContext->injectTemplateVariableContainer($this->templateVariableContainer);

		$this->controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->renderingContext->setControllerContext($this->controllerContext);

		$this->viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->injectViewHelperVariableContainer($this->viewHelperVariableContainer);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function constructorSetsViewHelperAndArguments() {
		$viewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper');
		$arguments = array('foo' => 'bar');
		$viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array($viewHelper, $arguments));

		$this->assertEquals(get_class($viewHelper), $viewHelperNode->getViewHelperClassName());
		$this->assertEquals($arguments, $viewHelperNode->_get('arguments'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		$childNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array('foo'));

		$mockViewHelper = $this->getMock('F3\Fluid\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render', 'prepareArguments'));

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());
		$viewHelperNode->addChildNode($childNode);

		$mockViewHelper->expects($this->once())->method('setChildNodes')->with($this->equalTo(array($childNode)));

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function initializeArgumentsAndRenderIsCalledByViewHelperNode() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('initializeArgumentsAndRender', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('initializeArgumentsAndRender');

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function initializeArgumentsAndRenderIsCalledWithCorrectArguments() {
		$arguments = array(
			'param0' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, FALSE),
			'param1' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, TRUE),
			'param2' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'string', 'Hallo', TRUE, null, TRUE)
		);

		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('initializeArgumentsAndRender', 'prepareArguments'));
		$mockViewHelper->expects($this->any())->method('prepareArguments')->will($this->returnValue($arguments));
		$mockViewHelper->expects($this->once())->method('initializeArgumentsAndRender')->with(array('param1' => 'a', 'param2' => 'b'));

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array(
			'param2' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('b'),
			'param1' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('a'),
		));

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesControllerContextToViewHelper() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setControllerContext'));
		$mockViewHelper->expects($this->once())->method('setControllerContext')->with($this->controllerContext);

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());
		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesViewHelperVariableContainerToViewHelper() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->once())->method('setViewHelperVariableContainer')->with($this->viewHelperVariableContainer);

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());
		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function multipleEvaluateCallsShareTheSameViewHelperInstance() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->any())->method('render')->will($this->returnValue('String'));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());
		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectManager->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));
		$this->mockObjectManager->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->evaluate($this->renderingContext);
		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertArgumentValueCallsConvertToBooleanForArgumentsOfTypeBoolean() {
		$viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('convertToBoolean'), array(), '', FALSE);
		$argumentViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array('evaluate'), array(), '', FALSE);
		$argumentViewHelperNode->expects($this->any())->method('evaluate')->will($this->returnValue('foo'));

		$viewHelperNode->expects($this->once())->method('convertToBoolean')->with('foo')->will($this->returnValue('bar'));

		$actualResult = $viewHelperNode->_call('convertArgumentValue', $argumentViewHelperNode, 'boolean', $this->renderingContext);
		$this->assertEquals('bar', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeBoolean() {
		$viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', FALSE));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', TRUE));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeString() {
		$viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', ''));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', 'false'));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', 'FALSE'));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', 'true'));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', 'TRUE'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsNumericValues() {
		$viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', 0));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', -1));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', '-1'));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', -.5));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', 1));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', .5));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeArray() {
		$viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', array()));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', array('foo')));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', array('foo' => 'bar')));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsObjects() {
		$viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', NULL));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', new \stdClass()));
	}
}

?>