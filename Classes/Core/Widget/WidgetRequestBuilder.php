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
 * Builds the WidgetRequest if an AJAX widget is called.
 *
 * @FLOW3\Scope("singleton")
 */
class WidgetRequestBuilder extends \TYPO3\FLOW3\Mvc\Web\RequestBuilder {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @var \TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	private $ajaxWidgetContextHolder;

	/**
	 * @param \TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder
	 * @return void
	 */
	public function injectAjaxWidgetContextHolder(\TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder $ajaxWidgetContextHolder) {
		$this->ajaxWidgetContextHolder = $ajaxWidgetContextHolder;
	}

	/**
	 * Builds a widget request object from the raw HTTP information
	 *
	 * @return \TYPO3\FLOW3\Mvc\ActionRequest The widget request as an object
	 */
	public function build() {
		$request = $this->objectManager->get('TYPO3\FLOW3\Mvc\ActionRequest');
		$request->setRequestUri($this->environment->getRequestUri());
		$request->setBaseUri($this->environment->getBaseUri());
		$request->setMethod($this->environment->getRequestMethod());
		$this->setArgumentsFromRawRequestData($request);

		$widgetId = $request->getInternalArgument('__widgetId');
		if ($widgetId !== NULL) {
			$widgetContext = $this->ajaxWidgetContextHolder->get($widgetId);
		} else {
			$serializedWidgetContextWithHmac = $request->getInternalArgument('__widgetContext');
			$serializedWidgetContext = $this->hashService->validateAndStripHmac($serializedWidgetContextWithHmac);
			$widgetContext = unserialize($serializedWidgetContext);
		}

		$request->setArgument('__widgetContext', $widgetContext);
		$request->setControllerObjectName($widgetContext->getControllerObjectName());
		return $request;
	}
}

?>