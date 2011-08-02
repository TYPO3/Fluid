<?php
namespace TYPO3\Fluid\Tests\Unit\View;

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

/**
 * Testcase for the TemplateView
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AbstractTemplateViewTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\View\AbstractTemplateView
	 */
	protected $view;

	/**
	 * @var \TYPO3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @var \TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * @var TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * Sets up this test case
	 * @return void
	 */
	public function setUp() {
		$this->templateVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer', array('exists', 'remove', 'add'));
		$this->viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer', array('setView'));
		$this->renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContext', array('getViewHelperVariableContainer', 'getTemplateVariableContainer'));
		$this->renderingContext->expects($this->any())->method('getViewHelperVariableContainer')->will($this->returnValue($this->viewHelperVariableContainer));
		$this->renderingContext->expects($this->any())->method('getTemplateVariableContainer')->will($this->returnValue($this->templateVariableContainer));
		$this->view = $this->getMock('TYPO3\Fluid\View\AbstractTemplateView', array('getTemplateSource', 'getLayoutSource', 'getPartialSource', 'canRender', 'getTemplateIdentifier', 'getLayoutIdentifier', 'getPartialIdentifier'));
		$this->view->setRenderingContext($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewIsPlacedInViewHelperVariableContainer() {
		$this->viewHelperVariableContainer->expects($this->once())->method('setView')->with($this->view);
		$this->view->setRenderingContext($this->renderingContext);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function assignAddsValueToTemplateVariableContainer() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('bar')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(3))->method('add')->with('bar', 'BarValue');

		$this->view
			->assign('foo', 'FooValue')
			->assign('bar', 'BarValue');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function assignCanOverridePreviouslyAssignedValues() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('foo')->will($this->returnValue(TRUE));
		$this->templateVariableContainer->expects($this->at(3))->method('remove')->with('foo');
		$this->templateVariableContainer->expects($this->at(4))->method('add')->with('foo', 'FooValueOverridden');

		$this->view->assign('foo', 'FooValue');
		$this->view->assign('foo', 'FooValueOverridden');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function assignMultipleAddsValuesToTemplateVariableContainer() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('bar')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(3))->method('add')->with('bar', 'BarValue');
		$this->templateVariableContainer->expects($this->at(4))->method('exists')->with('baz')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(5))->method('add')->with('baz', 'BazValue');

		$this->view
			->assignMultiple(array('foo' => 'FooValue', 'bar' => 'BarValue'))
			->assignMultiple(array('baz' => 'BazValue'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function assignMultipleCanOverridePreviouslyAssignedValues() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('foo')->will($this->returnValue(TRUE));
		$this->templateVariableContainer->expects($this->at(3))->method('remove')->with('foo');
		$this->templateVariableContainer->expects($this->at(4))->method('add')->with('foo', 'FooValueOverridden');
		$this->templateVariableContainer->expects($this->at(5))->method('exists')->with('bar')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(6))->method('add')->with('bar', 'BarValue');

		$this->view->assign('foo', 'FooValue');
		$this->view->assignMultiple(array('foo' => 'FooValueOverridden', 'bar' => 'BarValue'));
	}
}

?>