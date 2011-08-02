<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

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
class ViewHelperNodeTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * Rendering Context
	 * @var TYPO3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * Object factory mock
	 * @var TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * Template Variable Container
	 * @var TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 *
	 * @var TYPO3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;

	/**
	 * @var TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * Setup fixture
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->renderingContext = new \TYPO3\Fluid\Core\Rendering\RenderingContext();

		$this->mockObjectManager = $this->getMock('TYPO3\FLOW3\Object\ObjectManagerInterface');
		$this->renderingContext->injectObjectManager($this->mockObjectManager);

		$this->templateVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->renderingContext->injectTemplateVariableContainer($this->templateVariableContainer);

		$this->controllerContext = $this->getMock('TYPO3\FLOW3\MVC\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->renderingContext->setControllerContext($this->controllerContext);

		$this->viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->injectViewHelperVariableContainer($this->viewHelperVariableContainer);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function constructorSetsViewHelperAndArguments() {
		$viewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper');
		$arguments = array('foo' => 'bar');
		$viewHelperNode = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array($viewHelper, $arguments));

		$this->assertEquals(get_class($viewHelper), $viewHelperNode->getViewHelperClassName());
		$this->assertEquals($arguments, $viewHelperNode->_get('arguments'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		$childNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array('foo'));

		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render', 'prepareArguments'));

		$viewHelperNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());
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
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('initializeArgumentsAndRender', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('initializeArgumentsAndRender');

		$viewHelperNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function initializeArgumentsAndRenderIsCalledWithCorrectArguments() {
		$arguments = array(
			'param0' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, FALSE),
			'param1' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, TRUE),
			'param2' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'string', 'Hallo', TRUE, null, TRUE)
		);

		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('initializeArgumentsAndRender', 'prepareArguments'));
		$mockViewHelper->expects($this->any())->method('prepareArguments')->will($this->returnValue($arguments));
		$mockViewHelper->expects($this->once())->method('initializeArgumentsAndRender');

		$viewHelperNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array(
			'param2' => new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('b'),
			'param1' => new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('a'),
		));

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesRenderingContextToViewHelper() {
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setRenderingContext'));
		$mockViewHelper->expects($this->once())->method('setRenderingContext')->with($this->renderingContext);

		$viewHelperNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function multipleEvaluateCallsShareTheSameViewHelperInstance() {
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->any())->method('render')->will($this->returnValue('String'));

		$viewHelperNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($mockViewHelper, array());

		$viewHelperNode->evaluate($this->renderingContext);
		$viewHelperNode->evaluate($this->renderingContext);
	}
}

?>