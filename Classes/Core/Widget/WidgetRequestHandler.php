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
 * Widget request handler, which handles the request if
 * the argument "__widgetId" or "__widgetContext" is available in the GET/POST
 * parameters of the current request.
 *
 * This request handler does not override the standard handleRequest() method
 * but injects a specialized router which resolves the correct widget controller
 * by the given HTTP request.
 *
 * @FLOW3\Scope("singleton")
 * @FLOW3\Proxy("disable")
 */
class WidgetRequestHandler extends \TYPO3\FLOW3\Http\RequestHandler {

	/**
	 * @return boolean TRUE if it is an AJAX widget request
	 */
	public function canHandleRequest() {
		// We have to use $_GET and $_POST directly here, as the environment
		// is not yet initialized in canHandleRequest.
		return isset($_POST['__widgetId'])
			|| isset($_GET['__widgetId'])
			|| isset($_POST['__widgetContext'])
			|| isset($_GET['__widgetContext']);
	}

	/**
	 * This request handler has a higher priority than the default request handler.
	 *
	 * @return integer
	 */
	public function getPriority() {
		return 200;
	}

	/**
	 * Resolves a few dependencies of this request handler which can't be resolved
	 * automatically due to the early stage of the boot process this request handler
	 * is invoked at.
	 *
	 * @return void
	 */
	protected function resolveDependencies() {
		$objectManager = $this->bootstrap->getObjectManager();
		$this->securityContext = $objectManager->get('TYPO3\FLOW3\Security\Context');
		$this->dispatcher = $objectManager->get('TYPO3\FLOW3\Mvc\Dispatcher');
		$this->router = $objectManager->get('TYPO3\Fluid\Core\Widget\WidgetRouter');
	}
}

?>