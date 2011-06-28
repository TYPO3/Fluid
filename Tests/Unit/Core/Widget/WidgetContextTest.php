<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Widget;

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
 * Testcase for WidgetContext
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class WidgetContextTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var TYPO3\Fluid\Core\Widget\WidgetContext
	 */
	protected $widgetContext;

	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->widgetContext = new \TYPO3\Fluid\Core\Widget\WidgetContext();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function widgetIdentifierCanBeReadAgain() {
		$this->widgetContext->setWidgetIdentifier('myWidgetIdentifier');
		$this->assertEquals('myWidgetIdentifier', $this->widgetContext->getWidgetIdentifier());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function ajaxWidgetIdentifierCanBeReadAgain() {
		$this->widgetContext->setAjaxWidgetIdentifier(42);
		$this->assertEquals(42, $this->widgetContext->getAjaxWidgetIdentifier());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function nonAjaxWidgetConfigurationIsReturnedWhenContextIsNotSerialized() {
		$this->widgetContext->setNonAjaxWidgetConfiguration(array('key' => 'value'));
		$this->widgetContext->setAjaxWidgetConfiguration(array('keyAjax' => 'valueAjax'));
		$this->assertEquals(array('key' => 'value'), $this->widgetContext->getWidgetConfiguration());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function aWidgetConfigurationIsReturnedWhenContextIsSerialized() {
		$this->widgetContext->setNonAjaxWidgetConfiguration(array('key' => 'value'));
		$this->widgetContext->setAjaxWidgetConfiguration(array('keyAjax' => 'valueAjax'));
		$this->widgetContext = serialize($this->widgetContext);
		$this->widgetContext = unserialize($this->widgetContext);
		$this->assertEquals(array('keyAjax' => 'valueAjax'), $this->widgetContext->getWidgetConfiguration());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function controllerObjectNameCanBeReadAgain() {
		$this->widgetContext->setControllerObjectName('TYPO3\My\Object\Name');
		$this->assertEquals('TYPO3\My\Object\Name', $this->widgetContext->getControllerObjectName());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperChildNodesCanBeReadAgain() {
		$viewHelperChildNodes = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		$renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');

		$this->widgetContext->setViewHelperChildNodes($viewHelperChildNodes, $renderingContext);
		$this->assertSame($viewHelperChildNodes, $this->widgetContext->getViewHelperChildNodes());
		$this->assertSame($renderingContext, $this->widgetContext->getViewHelperChildNodeRenderingContext());
	}
}
?>