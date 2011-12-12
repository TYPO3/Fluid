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
 * the argument "__widgetId" or "__widgetContext" is available in the GET/POST parameters of the current request.
 *
 * This Request Handler gets the WidgetRequestBuilder injected.
 *
 * @FLOW3\Scope("singleton")
 */
class WidgetRequestHandler extends \TYPO3\FLOW3\MVC\Web\RequestHandler {

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
	 * Handles a HTTP request
	 *
	 * @return void
	 */
	public function handleRequest() {
		$sequence = $this->bootstrap->buildRuntimeSequence();
		$sequence->invoke($this->bootstrap);

		$objectManager = $this->bootstrap->getObjectManager();

		$this->request = $objectManager->get('TYPO3\Fluid\Core\Widget\WidgetRequestBuilder')->build();
		$response = new \TYPO3\FLOW3\MVC\Web\Response();

		$dispatcher = $objectManager->get('TYPO3\FLOW3\MVC\Dispatcher');
		$dispatcher->dispatch($this->request, $response);

		$response->send();
		$this->bootstrap->shutdown('Runtime');
		$this->exit->__invoke();
	}
}

?>