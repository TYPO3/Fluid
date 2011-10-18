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
 * typo3-fluid-widget-id is found.
 *
 * This Request Handler gets the WidgetRequestBuilder injected.
 *
 * @FLOW3\Scope("singleton")
 */
class WidgetRequestHandler extends \TYPO3\FLOW3\MVC\Web\RequestHandler {

	/**
	 * @var \TYPO3\FLOW3\Utility\Environment
	 */
	protected $environment;

	/**
	 * @param \TYPO3\FLOW3\Utility\Environment $environment
	 * @return void
	 */
	public function injectEnvironment(\TYPO3\FLOW3\Utility\Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * @return boolean TRUE if it is an AJAX widget request
	 */
	public function canHandleRequest() {
		$rawGetArguments = $this->environment->getRawGetArguments();
		return isset($rawGetArguments['typo3-fluid-widget-id']);
	}

	/**
	 * This request handler has a higher priority than the default request handler.
	 *
	 * @return integer
	 */
	public function getPriority() {
		return 200;
	}
}

?>