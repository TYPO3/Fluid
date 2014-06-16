<?php
namespace TYPO3\Fluid\Core\Widget;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Exception\ForwardException;
use TYPO3\Flow\Mvc\Exception\InfiniteLoopException;
use TYPO3\Flow\Mvc\Exception\StopActionException;
use TYPO3\Flow\Object\DependencyInjection\DependencyProxy;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface;
use TYPO3\Fluid\Core\Widget\Exception\InvalidControllerException;
use TYPO3\Fluid\Core\Widget\Exception\MissingControllerException;

/**
 * @api
 */
abstract class AbstractWidgetViewHelper extends AbstractViewHelper implements ChildNodeAccessInterface {

	/**
	 * The Controller associated to this widget.
	 * This needs to be filled by the individual subclass using
	 * property injection.
	 *
	 * @var AbstractWidgetController
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
	 * If set to FALSE, this widget won't create a session (only relevant for AJAX widgets).
	 *
	 * You then need to manually add the serialized configuration data to your links, by
	 * setting "includeWidgetContext" to TRUE in the widget link and URI ViewHelpers.
	 *
	 * @var boolean
	 * @api
	 */
	protected $storeConfigurationInSession = TRUE;

	/**
	 * @var AjaxWidgetContextHolder
	 */
	private $ajaxWidgetContextHolder;

	/**
	 * @var WidgetContext
	 */
	private $widgetContext;

	/**
	 * @param AjaxWidgetContextHolder $ajaxWidgetContextHolder
	 * @return void
	 */
	public function injectAjaxWidgetContextHolder(AjaxWidgetContextHolder $ajaxWidgetContextHolder) {
		$this->ajaxWidgetContextHolder = $ajaxWidgetContextHolder;
	}

	/**
	 * @param WidgetContext $widgetContext
	 * @return void
	 */
	public function injectWidgetContext(WidgetContext $widgetContext) {
		$this->widgetContext = $widgetContext;
	}

	/**
	 * Registers the widgetId viewhelper
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('widgetId', 'string', 'Unique identifier of the widget instance');
	}

	/**
	 * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
	 *
	 * @return string the rendered ViewHelper.
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
	 */
	private function initializeWidgetContext() {
		if ($this->ajaxWidget === TRUE) {
			if ($this->storeConfigurationInSession === TRUE) {
				$this->ajaxWidgetContextHolder->store($this->widgetContext);
			}
			$this->widgetContext->setAjaxWidgetConfiguration($this->getAjaxWidgetConfiguration());
		}

		$this->widgetContext->setNonAjaxWidgetConfiguration($this->getNonAjaxWidgetConfiguration());
		$this->initializeWidgetIdentifier();

		$controllerObjectName = ($this->controller instanceof DependencyProxy) ? $this->controller->_getClassName() : get_class($this->controller);
		$this->widgetContext->setControllerObjectName($controllerObjectName);
	}

	/**
	 * Stores the syntax tree child nodes in the Widget Context, so they can be
	 * rendered with <f:widget.renderChildren> lateron.
	 *
	 * @param array $childNodes The SyntaxTree Child nodes of this ViewHelper.
	 * @return void
	 */
	public function setChildNodes(array $childNodes) {
		/** @var $rootNode RootNode */
		$rootNode = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		foreach ($childNodes as $childNode) {
			$rootNode->addChildNode($childNode);
		}
		$this->widgetContext->setViewHelperChildNodes($rootNode, $this->renderingContext);
	}

	/**
	 * Generate the configuration for this widget. Override to adjust.
	 *
	 * @return array
	 * @api
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
	 * @return Response the response of this request.
	 * @throws Exception\MissingControllerException
	 * @throws \TYPO3\Flow\Mvc\Exception\StopActionException
	 * @throws \TYPO3\Flow\Mvc\Exception\InfiniteLoopException
	 * @api
	 */
	protected function initiateSubRequest() {
		if ($this->controller instanceof DependencyProxy) {
			$this->controller->_activateDependency();
		}
		if (!($this->controller instanceof AbstractWidgetController)) {
			throw new Exception\MissingControllerException('initiateSubRequest() can not be called if there is no controller inside $this->controller. Make sure to add the @TYPO3\Flow\Annotations\Inject annotation in your widget class.', 1284401632);
		}

		/** @var $subRequest ActionRequest */
		$subRequest = $this->objectManager->get('TYPO3\Flow\Mvc\ActionRequest', $this->controllerContext->getRequest());
		/** @var $subResponse Response */
		$subResponse = $this->objectManager->get('TYPO3\Flow\Http\Response', $this->controllerContext->getResponse());

		$this->passArgumentsToSubRequest($subRequest);
		$subRequest->setArgument('__widgetContext', $this->widgetContext);
		$subRequest->setArgumentNamespace('--' . $this->widgetContext->getWidgetIdentifier());

		$dispatchLoopCount = 0;
		while (!$subRequest->isDispatched()) {
			if ($dispatchLoopCount++ > 99) {
				throw new InfiniteLoopException('Could not ultimately dispatch the widget request after '  . $dispatchLoopCount . ' iterations.', 1380282310);
			}
			$widgetControllerObjectName = $this->widgetContext->getControllerObjectName();
			if ($subRequest->getControllerObjectName() !== '' && $subRequest->getControllerObjectName() !== $widgetControllerObjectName) {
				throw new Exception\InvalidControllerException(sprintf('You are not allowed to initiate requests to different controllers from a widget.' . chr(10) . 'widget controller: "%s", requested controller: "%s".', $widgetControllerObjectName, $subRequest->getControllerObjectName()), 1380284579);
			}
			$subRequest->setControllerObjectName($this->widgetContext->getControllerObjectName());
			try {
				$this->controller->processRequest($subRequest, $subResponse);
			} catch (StopActionException $exception) {
				if ($exception instanceof ForwardException) {
					$subRequest = $exception->getNextRequest();
					continue;
				}
				/** @var $parentResponse Response */
				$parentResponse = $this->controllerContext->getResponse();
				$parentResponse
					->setStatus($subResponse->getStatusCode())
					->setContent($subResponse->getContent())
					->setHeader('Location', $subResponse->getHeader('Location'));
				throw $exception;
			}
		}
		return $subResponse;
	}

	/**
	 * Pass the arguments of the widget to the sub request.
	 *
	 * @param ActionRequest $subRequest
	 * @return void
	 */
	private function passArgumentsToSubRequest(ActionRequest $subRequest) {
		$arguments = $this->controllerContext->getRequest()->getPluginArguments();
		$widgetIdentifier = $this->widgetContext->getWidgetIdentifier();

		$controllerActionName = 'index';
		if (isset($arguments[$widgetIdentifier])) {
			if (isset($arguments[$widgetIdentifier]['@action'])) {
				$controllerActionName = $arguments[$widgetIdentifier]['@action'];
				unset($arguments[$widgetIdentifier]['@action']);
			}
			$subRequest->setArguments($arguments[$widgetIdentifier]);
		}
		if ($subRequest->getControllerActionName() === NULL) {
			$subRequest->setControllerActionName($controllerActionName);
		}
	}

	/**
	 * The widget identifier is unique on the current page, and is used
	 * in the URI as a namespace for the widget's arguments.
	 *
	 * @return string the widget identifier for this widget
	 * @return void
	 */
	private function initializeWidgetIdentifier() {
		$widgetIdentifier = ($this->hasArgument('widgetId') ? $this->arguments['widgetId'] : strtolower(str_replace('\\', '-', get_class($this))));
		$this->widgetContext->setWidgetIdentifier($widgetIdentifier);
	}

	/**
	 * Resets the ViewHelper state by creating a fresh WidgetContext
	 *
	 * @return void
	 */
	public function resetState() {
		if ($this->ajaxWidget) {
			$this->widgetContext = $this->objectManager->get('TYPO3\Fluid\Core\Widget\WidgetContext');
		}
	}

}
