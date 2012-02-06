<?php
namespace TYPO3\Fluid\Core\Widget;

/*
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A specialized router which can create ActionRequests for requests directed to
 * a widget. The requests handled by this router are typically AJAX requests.
 *
 * @FLOW3\Scope("singleton")
 */
class WidgetRouter {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	protected $ajaxWidgetContextHolder;

	/**
	 * Builds a widget request object from the given HTTP request
	 *
	 * @param \TYPO3\FLOW3\Http\Request $httpRequest
	 * @return \TYPO3\FLOW3\Mvc\ActionRequest
	 */
	public function route(\TYPO3\FLOW3\Http\Request $httpRequest) {
		$actionRequest = $httpRequest->createActionRequest();
		$widgetId = $actionRequest->getInternalArgument('__widgetId');
		if ($widgetId !== NULL) {
			$widgetContext = $this->ajaxWidgetContextHolder->get($widgetId);
		} else {
			$serializedWidgetContextWithHmac = $actionRequest->getInternalArgument('__widgetContext');
			$serializedWidgetContext = $this->hashService->validateAndStripHmac($serializedWidgetContextWithHmac);
			$widgetContext = unserialize($serializedWidgetContext);
		}

		$actionRequest->setArgument('__widgetContext', $widgetContext);
		$actionRequest->setControllerObjectName($widgetContext->getControllerObjectName());
		return $actionRequest;
	}

	/**
	 * Currently not implemented.
	 *
	 * @param array $routeValues
	 * @return string URI
	 * @codeCoverageIgnore
	 */
	public function resolve(array $routeValues) {
		throw new Exception('resolve() is currently not implemented in the WidgetRouter.', 1332785251);
	}

	/**
	 * Currently not implemented.
	 *
	 * @param string $packageKey
	 * @param string $subPackageKey
	 * @param string $controllerName
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getControllerObjectName($packageKey, $subPackageKey, $controllerName) {
		throw new Exception('getControllerObjectName() is currently not implemented in the WidgetRouter.', 1332785252);
	}

}

?>
