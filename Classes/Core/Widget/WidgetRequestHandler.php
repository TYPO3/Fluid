<?php
namespace TYPO3\Fluid\Core\Widget;

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
 * Widget request handler, which handles the request if
 * typo3-fluid-widget-id is found.
 *
 * This Request Handler gets the WidgetRequestBuilder injected.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
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
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function canHandleRequest() {
		$rawGetArguments = $this->environment->getRawGetArguments();
		return isset($rawGetArguments['typo3-fluid-widget-id']);
	}

	/**
	 * This request handler has a higher priority than the default request handler.
	 *
	 * @return integer
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getPriority() {
		return 200;
	}
}

?>