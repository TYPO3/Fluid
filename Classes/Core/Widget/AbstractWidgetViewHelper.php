<?php
namespace TYPO3\Fluid\Core\Widget;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
abstract class AbstractWidgetViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper implements \TYPO3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface {

	/**
	 * The Controller associated to this widget.
	 * This needs to be filled by the individual subclass by an @inject
	 * annotation.
	 *
	 * @var TYPO3\Fluid\Core\Widget\AbstractWidgetController
	 * @api
	 */
	protected $controller;

	/**
	 * If set to TRUE, it is an AJAX widget.
	 *
	 * @var boolean
	 * @api
	 */
	protected $ajaxWidget = FALSE;

	/**
	 * @var TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	private $ajaxWidgetContextHolder;

	/**
	 * @var TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	private $objectManager;

	/**
	 * @var TYPO3\Fluid\Core\Widget\WidgetContext
	 */
	private $widgetContext;

	/**
	 * @param \TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectAjaxWidgetContextHolder(\TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder) {
		$this->ajaxWidgetContextHolder = $ajaxWidgetContextHolder;
	}

	/**
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectObjectManager(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\Fluid\Core\Widget\WidgetContext $widgetContext
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectWidgetContext(\TYPO3\Fluid\Core\Widget\WidgetContext $widgetContext) {
		$this->widgetContext = $widgetContext;
	}

	/**
	 * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
	 *
	 * @return string the rendered ViewHelper.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArgumentsAndRender() {
		$this->validateArguments();
		$this->initialize();
		$this->initializeWidgetContext();

		return $this->callRenderMethod();
	}

	/**
	 * Initialize the Widget Context, before the Render method is called.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	private function initializeWidgetContext() {
		if ($this->ajaxWidget === TRUE) {
			$this->ajaxWidgetContextHolder->store($this->widgetContext);
			$this->widgetContext->setAjaxWidgetConfiguration($this->getAjaxWidgetConfiguration());
		}

		$this->widgetContext->setNonAjaxWidgetConfiguration($this->getNonAjaxWidgetConfiguration());
		$this->initializeWidgetIdentifier();

		$controllerObjectName = get_class($this->controller);
		$this->widgetContext->setControllerObjectName($controllerObjectName);
	}

	/**
	 * Stores the syntax tree child nodes in the Widget Context, so they can be
	 * rendered with <f:widget.renderChildren> lateron.
	 *
	 * @param array $childNodes The SyntaxTree Child nodes of this ViewHelper.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setChildNodes(array $childNodes) {
		$rootNode = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		foreach ($childNodes as $childNode) {
			$rootNode->addChildNode($childNode);
		}
		$this->widgetContext->setViewHelperChildNodes($rootNode, $this->getRenderingContext());
	}

	/**
	 * Generate the configuration for this widget. Override to adjust.
	 *
	 * @return array
	 * @api
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getWidgetConfiguration() {
		return $this->arguments;
	}

	/**
	 * Generate the configuration for this widget in AJAX context.
	 *
	 * By default, returns getWidgetConfiguration(). Should become API later.
	 *
	 * @return array
	 */
	protected function getAjaxWidgetConfiguration() {
		return $this->getWidgetConfiguration();
	}

	/**
	 * Generate the configuration for this widget in non-AJAX context.
	 *
	 * By default, returns getWidgetConfiguration(). Should become API later.
	 *
	 * @return array
	 */
	protected function getNonAjaxWidgetConfiguration() {
		return $this->getWidgetConfiguration();
	}

	/**
	 * Initiate a sub request to $this->controller. Make sure to fill $this->controller
	 * via Dependency Injection.
	 *
	 * @return \TYPO3\FLOW3\MVC\ResponseInterface the response of this request.
	 * @api
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function initiateSubRequest() {
		if (!($this->controller instanceof \TYPO3\Fluid\Core\Widget\AbstractWidgetController)) {
			throw new \TYPO3\Fluid\Core\Widget\Exception\MissingControllerException('initiateSubRequest() can not be called if there is no controller inside $this->controller. Make sure to add the @inject annotation in your widget class.', 1284401632);
		}

		$subRequest = $this->objectManager->create('TYPO3\FLOW3\MVC\Web\SubRequest', $this->controllerContext->getRequest());
		$this->passArgumentsToSubRequest($subRequest);
		$subRequest->setArgument('__widgetContext', $this->widgetContext);
		$subRequest->setControllerObjectName($this->widgetContext->getControllerObjectName());
		$subRequest->setArgumentNamespace($this->widgetContext->getWidgetIdentifier());

		$subResponse = $this->objectManager->create('TYPO3\FLOW3\MVC\Web\Response');
		$this->controller->processRequest($subRequest, $subResponse);
		return $subResponse;
	}

	/**
	 * Pass the arguments of the widget to the subrequest.
	 *
	 * @param \TYPO3\FLOW3\MVC\Web\Request $subRequest
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	private function passArgumentsToSubRequest(\TYPO3\FLOW3\MVC\Web\Request $subRequest) {
		$arguments = $this->controllerContext->getRequest()->getArguments();
		$widgetIdentifier = $this->widgetContext->getWidgetIdentifier();

		$controllerActionName = 'index';
		if (isset($arguments[$widgetIdentifier])) {
			if (isset($arguments[$widgetIdentifier]['action'])) {
				$controllerActionName = $arguments[$widgetIdentifier]['action'];
				unset($arguments[$widgetIdentifier]['action']);
			}
			$subRequest->setArguments($arguments[$widgetIdentifier]);
		}
		$subRequest->setControllerActionName($controllerActionName);
	}

	/**
	 * The widget identifier is unique on the current page, and is used
	 * in the URI as a namespace for the widget's arguments.
	 *
	 * @return string the widget identifier for this widget
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @return void
	 * @todo clean up, and make it somehow more routing compatible.
	 */
	private function initializeWidgetIdentifier() {
		if (!$this->viewHelperVariableContainer->exists('TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper', 'nextWidgetNumber')) {
			$widgetCounter = 0;
		} else {
			$widgetCounter = $this->viewHelperVariableContainer->get('TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper', 'nextWidgetNumber');
		}
		$widgetIdentifier = '@widget_' . $widgetCounter;
		$this->viewHelperVariableContainer->addOrUpdate('TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper', 'nextWidgetNumber', $widgetCounter + 1);

		$this->widgetContext->setWidgetIdentifier($widgetIdentifier);
	}
}

?>