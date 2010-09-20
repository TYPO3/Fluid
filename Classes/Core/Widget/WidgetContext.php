<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Widget;

/*
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
 * The WidgetContext stores all information a widget needs to know about the
 * environment.
 *
 * The WidgetContext can be fetched from the current WidgetRequest, and is thus
 * available throughout the whole sub-request of the widget. It is used internally
 * by various ViewHelpers (like <f:link.widget>, <f:uri.widget>, <f:widget.renderChildren>),
 * to get knowledge over the current widget's configuration.
 *
 * It is a purely internal class which should not be used outside of Fluid.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class WidgetContext {

	/**
	 * Uniquely idenfies a Widget Instance on a certain page.
	 *
	 * @var string
	 */
	protected $widgetIdentifier;

	/**
	 * Per-User unique identifier of the widget, if it is an AJAX widget.
	 *
	 * @var integer
	 */
	protected $ajaxWidgetIdentifier;

	/**
	 * User-supplied widget configuration, available inside the widget
	 * controller as $this->widgetConfiguration.
	 *
	 * @var array
	 */
	protected $widgetConfiguration;

	/**
	 * The fully qualified object name of the Controller which this widget uses.
	 *
	 * @var string
	 */
	protected $controllerObjectName;

	/**
	 * The child nodes of the Widget ViewHelper.
	 * Only available inside non-AJAX requests.
	 *
	 * @var F3\Fluid\Core\Parser\SyntaxTree\RootNode
	 * @transient
	 */
	protected $viewHelperChildNodes; // TODO: rename to something more meaningful.

	/**
	 * The rendering context of the ViewHelperChildNodes.
	 * Only available inside non-AJAX requests.
	 *
	 * @var F3\Fluid\Core\Rendering\RenderingContextInterface
	 * @transient
	 */
	protected $viewHelperChildNodeRenderingContext;

	/**
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getWidgetIdentifier() {
		return $this->widgetIdentifier;
	}

	/**
	 * @param string $widgetIdentifier
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setWidgetIdentifier($widgetIdentifier) {
		$this->widgetIdentifier = $widgetIdentifier;
	}

	/**
	 * @return integer
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getAjaxWidgetIdentifier() {
		return $this->ajaxWidgetIdentifier;
	}

	/**
	 * @param integer $ajaxWidgetIdentifier
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setAjaxWidgetIdentifier($ajaxWidgetIdentifier) {
		$this->ajaxWidgetIdentifier = $ajaxWidgetIdentifier;
	}

	/**
	 * @return array
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getWidgetConfiguration() {
		return $this->widgetConfiguration;
	}

	/**
	 * @param array $widgetConfiguration
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setWidgetConfiguration($widgetConfiguration) {
		$this->widgetConfiguration = $widgetConfiguration;
	}

	/**
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getControllerObjectName() {
		return $this->controllerObjectName;
	}

	/**
	 * @param string $controllerObjectName
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setControllerObjectName($controllerObjectName) {
		$this->controllerObjectName = $controllerObjectName;
	}

	/**
	 * @param F3\Fluid\Core\Parser\SyntaxTree\RootNode $viewHelperChildNodes
	 * @param F3\Fluid\Core\Rendering\RenderingContextInterface $viewHelperChildNodeRenderingContext
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setViewHelperChildNodes(\F3\Fluid\Core\Parser\SyntaxTree\RootNode $viewHelperChildNodes, \F3\Fluid\Core\Rendering\RenderingContextInterface $viewHelperChildNodeRenderingContext) {
		$this->viewHelperChildNodes = $viewHelperChildNodes;
		$this->viewHelperChildNodeRenderingContext = $viewHelperChildNodeRenderingContext;
	}

	/**
	 * @return F3\Fluid\Core\Parser\SyntaxTree\RootNode
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperChildNodes() {
		return $this->viewHelperChildNodes;
	}

	/**
	 * @return F3\Fluid\Core\Rendering\RenderingContextInterface
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperChildNodeRenderingContext() {
		return $this->viewHelperChildNodeRenderingContext;
	}
}
?>