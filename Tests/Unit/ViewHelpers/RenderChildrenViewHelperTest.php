<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Testcase for CycleViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RenderChildrenViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \F3\Fluid\ViewHelpers\RenderChildrenViewHelper
	 */
	protected $viewHelper;

	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->viewHelper = $this->getMock('F3\Fluid\ViewHelpers\RenderChildrenViewHelper', array('renderChildren'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderCallsEvaluateOnTheRootNodeAndRegistersTheArguments() {
		$this->request = $this->getMock('F3\Fluid\Core\Widget\WidgetRequest');
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->viewHelper->setControllerContext($this->controllerContext);
		$this->viewHelper->initializeArguments();

		$templateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$templateVariableContainer->expects($this->at(0))->method('add')->with('k1', 'v1');
		$templateVariableContainer->expects($this->at(1))->method('add')->with('k2', 'v2');
		$templateVariableContainer->expects($this->at(2))->method('remove')->with('k1');
		$templateVariableContainer->expects($this->at(3))->method('remove')->with('k2');

		$renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContextInterface');
		$renderingContext->expects($this->any())->method('getTemplateVariableContainer')->will($this->returnValue($templateVariableContainer));

		$rootNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\RootNode');

		$widgetContext = $this->getMock('F3\Fluid\Core\Widget\WidgetContext');
		$this->request->expects($this->any())->method('getWidgetContext')->will($this->returnValue($widgetContext));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodeRenderingContext')->will($this->returnValue($renderingContext));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodes')->will($this->returnValue($rootNode));

		$rootNode->expects($this->any())->method('evaluate')->with($renderingContext)->will($this->returnValue('Rendered Results'));

		$output = $this->viewHelper->render(array('k1' => 'v1', 'k2' => 'v2'));
		$this->assertEquals('Rendered Results', $output);
	}

	/**
	 * @test
	 * @expectedException F3\Fluid\Core\Widget\Exception\WidgetRequestNotFoundException
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderThrowsExceptionIfTheRequestIsNotAWidgetRequest() {
		$this->request = $this->getMock('F3\FLOW3\MVC\Request');
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->viewHelper->setControllerContext($this->controllerContext);
		$this->viewHelper->initializeArguments();

		$output = $this->viewHelper->render();
	}

	/**
	 * @test
	 * @expectedException F3\Fluid\Core\Widget\Exception\RenderingContextNotFoundException
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderThrowsExceptionIfTheChildNodeRenderingContextIsNotThere() {
		$this->request = $this->getMock('F3\Fluid\Core\Widget\WidgetRequest');
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->viewHelper->setControllerContext($this->controllerContext);
		$this->viewHelper->initializeArguments();

		$widgetContext = $this->getMock('F3\Fluid\Core\Widget\WidgetContext');
		$this->request->expects($this->any())->method('getWidgetContext')->will($this->returnValue($widgetContext));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodeRenderingContext')->will($this->returnValue(NULL));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodes')->will($this->returnValue(NULL));

		$output = $this->viewHelper->render();
	}
}
?>