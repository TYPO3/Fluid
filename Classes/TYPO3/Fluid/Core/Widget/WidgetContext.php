<?php
namespace TYPO3\Fluid\Core\Widget;

/*
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * The WidgetContext stores all information a widget needs to know about the
 * environment.
 *
 * The WidgetContext can be fetched from the current request as internal argument __widgetContext,
 * and is thus available throughout the whole sub-request of the widget. It is used internally
 * by various ViewHelpers (like <f:widget.link>, <f:widget.link>, <f:widget.renderChildren>),
 * to get knowledge over the current widget's configuration.
 *
 * It is a purely internal class which should not be used outside of Fluid.
 *
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
	 * controller as $this->widgetConfiguration, if being inside an AJAX
	 * request
	 *
	 * @var array
	 */
	protected $ajaxWidgetConfiguration;

	/**
	 * User-supplied widget configuration, available inside the widget
	 * controller as $this->widgetConfiguration, if being inside a non-AJAX
	 * request
	 *
	 * @var array
	 */
	protected $nonAjaxWidgetConfiguration;
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
	 * TODO: rename to something more meaningful.
	 *
	 * @var RootNode
	 * @Flow\Transient
	 */
	protected $viewHelperChildNodes;

	/**
	 * The rendering context of the ViewHelperChildNodes.
	 * Only available inside non-AJAX requests.
	 *
	 * @var RenderingContextInterface
	 * @Flow\Transient
	 */
	protected $viewHelperChildNodeRenderingContext;

	/**
	 * @return string
	 */
	public function getWidgetIdentifier() {
		return $this->widgetIdentifier;
	}

	/**
	 * @param string $widgetIdentifier
	 * @return void
	 */
	public function setWidgetIdentifier($widgetIdentifier) {
		$this->widgetIdentifier = $widgetIdentifier;
	}

	/**
	 * @return integer
	 */
	public function getAjaxWidgetIdentifier() {
		return $this->ajaxWidgetIdentifier;
	}

	/**
	 * @param integer $ajaxWidgetIdentifier
	 * @return void
	 */
	public function setAjaxWidgetIdentifier($ajaxWidgetIdentifier) {
		$this->ajaxWidgetIdentifier = $ajaxWidgetIdentifier;
	}

	/**
	 * @return array
	 */
	public function getWidgetConfiguration() {
		if ($this->nonAjaxWidgetConfiguration !== NULL) {
			return $this->nonAjaxWidgetConfiguration;
		} else {
			return $this->ajaxWidgetConfiguration;
		}
	}

	/**
	 * @param array $ajaxWidgetConfiguration
	 * @return void
	 */
	public function setAjaxWidgetConfiguration(array $ajaxWidgetConfiguration) {
		$this->ajaxWidgetConfiguration = $ajaxWidgetConfiguration;
	}

	/**
	 * @param array $nonAjaxWidgetConfiguration
	 * @return void
	 */
	public function setNonAjaxWidgetConfiguration(array $nonAjaxWidgetConfiguration) {
		$this->nonAjaxWidgetConfiguration = $nonAjaxWidgetConfiguration;
	}

	/**
	 * @return string
	 */
	public function getControllerObjectName() {
		return $this->controllerObjectName;
	}

	/**
	 * @param string $controllerObjectName
	 * @return void
	 */
	public function setControllerObjectName($controllerObjectName) {
		$this->controllerObjectName = $controllerObjectName;
	}

	/**
	 * @param RootNode $viewHelperChildNodes
	 * @param RenderingContextInterface $viewHelperChildNodeRenderingContext
	 * @return void
	 */
	public function setViewHelperChildNodes(RootNode $viewHelperChildNodes, RenderingContextInterface $viewHelperChildNodeRenderingContext) {
		$this->viewHelperChildNodes = $viewHelperChildNodes;
		$this->viewHelperChildNodeRenderingContext = $viewHelperChildNodeRenderingContext;
	}

	/**
	 * @return RootNode
	 */
	public function getViewHelperChildNodes() {
		return $this->viewHelperChildNodes;
	}

	/**
	 * @return RenderingContextInterface
	 */
	public function getViewHelperChildNodeRenderingContext() {
		return $this->viewHelperChildNodeRenderingContext;
	}

	/**
	 * Serialize everything *except* the viewHelperChildNodes, viewHelperChildNodeRenderingContext and nonAjaxWidgetConfiguration
	 *
	 * @return array
	 */
	public function __sleep() {
		return array('widgetIdentifier', 'ajaxWidgetIdentifier', 'ajaxWidgetConfiguration', 'controllerObjectName');
	}
}
