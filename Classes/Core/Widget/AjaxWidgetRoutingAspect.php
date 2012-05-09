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
use TYPO3\FLOW3\Aop\JoinPointInterface;
use TYPO3\FLOW3\Mvc\ActionRequest;

/**
 * Aspect which intercepts the regular routing mechanism and creates a matching
 * ActoinRequest if an AJAX widget request was detected.
 *
 * @FLOW3\Scope("singleton")
 * @FLOW3\Aspect
 */
class AjaxWidgetRoutingAspect {

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
	 * An advice which intercepts the original route() method if a widget AJAX request
	 * was identified.
	 *
	 * If the HTTP request contains an argument hinting on an AJAX request directed
	 * to a widget, this method will create a matching ActionRequest rather than
	 * invoking the whole routing mechanism.
	 *
	 * @FLOW3\Around("method(TYPO3\FLOW3\Mvc\Routing\Router->route())")
	 * @param \TYPO3\FLOW3\Aop\JoinPointInterface $joinPoint
	 * @return \TYPO3\FLOW3\Mvc\ActionRequest
	 */
	public function routeAjaxWidgetRequestAdvice(JoinPointInterface $joinPoint) {
		$httpRequest = $joinPoint->getMethodArgument('httpRequest');

		if ($httpRequest->hasArgument('__widgetId') || $httpRequest->hasArgument('__widgetContext')) {
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
		} else {
			return $joinPoint->getAdviceChain()->proceed($joinPoint);
		}
	}

}

?>