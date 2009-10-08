<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @version $Id$
 */
/**
 * Testcase for [insert classname here]
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
include_once(__DIR__ . '/../Fixtures/ChildNodeAccessFacetViewHelper.php');
class ViewHelperNodeTest extends \F3\Testing\BaseTestCase {

	/**
	 * Rendering Context
	 * @var F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * Object factory mock
	 * @var F3\FLOW3\Object\FactoryInterface
	 */
	protected $mockObjectFactory;

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

		$this->mockObjectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');
		$this->renderingContext->injectObjectFactory($this->mockObjectFactory);

		$this->templateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->renderingContext->setTemplateVariableContainer($this->templateVariableContainer);

		$this->controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext');
		$this->renderingContext->setControllerContext($this->controllerContext);

		$this->viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function constructorSetsViewHelperClassNameAndArguments() {
		$viewHelperClassName = 'MyViewHelperClassName';
		$arguments = array('foo' => 'bar');
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('dummy'), array($viewHelperClassName, $arguments));

		$this->assertEquals($viewHelperClassName, $viewHelperNode->getViewHelperClassName());
		$this->assertEquals($arguments, $viewHelperNode->_get('arguments'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		$childNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array('foo'));

		$mockViewHelper = $this->getMock('F3\Fluid\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render', 'prepareArguments', 'setRenderingContext', 'isObjectAccessorPostProcessorEnabled'));

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\ViewHelpers\TestViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\ViewHelpers\TestViewHelper', array());
		$viewHelperNode->addChildNode($childNode);

		$mockViewHelper->expects($this->once())->method('setChildNodes')->with($this->equalTo(array($childNode)));
		$mockViewHelper->expects($this->once())->method('isObjectAccessorPostProcessorEnabled')->will($this->returnValue(TRUE));
		//$mockViewHelper->expects($this->once())->method('setRenderingContext')->with($this->renderingContext);

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function validateArgumentsIsCalledByViewHelperNode() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('validateArguments');

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderMethodIsCalledWithCorrectArguments() {
		$arguments = array(
			'param0' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, FALSE),
			'param1' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, TRUE),
			'param2' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'string', 'Hallo', TRUE, null, TRUE)
		);

		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue($arguments));
		$mockViewHelper->expects($this->once())->method('render')->with('a', 'b');

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array(
			'param2' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('b'),
			'param1' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('a'),
		));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesControllerContextToViewHelper() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setControllerContext'));
		$mockViewHelper->expects($this->once())->method('setControllerContext')->with($this->controllerContext);

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());
		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesViewHelperVariableContainerToViewHelper() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->once())->method('setViewHelperVariableContainer')->with($this->viewHelperVariableContainer);

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());
		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function multipleEvaluateCallsShareTheSameViewHelperInstance() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->any())->method('render')->will($this->returnValue('String'));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());
		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));
		$this->mockObjectFactory->expects($this->at(2))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertArgumentValueCallsConvertToBooleanForArgumentsOfTypeBoolean() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('convertToBoolean'), array(), '', FALSE);
		$viewHelperNode->_set('renderingContext', $this->renderingContext);
		$argumentViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array('evaluate'), array(), '', FALSE);
		$argumentViewHelperNode->expects($this->once())->method('evaluate')->will($this->returnValue('foo'));

		$viewHelperNode->expects($this->once())->method('convertToBoolean')->with('foo')->will($this->returnValue('bar'));

		$actualResult = $viewHelperNode->_call('convertArgumentValue', $argumentViewHelperNode, 'boolean');
		$this->assertEquals('bar', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeBoolean() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', FALSE));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', TRUE));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeString() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('dummy'), array(), '', FALSE);

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
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', 0));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', -1));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', -.5));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', 1));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', .5));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeArray() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', array()));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', array('foo')));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', array('foo' => 'bar')));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsObjects() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', NULL));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', new \stdClass()));
	}
}

?>