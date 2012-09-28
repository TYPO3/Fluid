<?php
namespace TYPO3\Fluid\Tests\Functional\Core\Fixtures\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * This is a regular action controller which serves as the starting point for testing
 * the AJAX widget functionality. It has nothing to do with the actual AJAX call. It
 * simulates the place where you'd integrate an AJAX widget and helps us rendering
 * a valid URI we can use for sending an AJAX request to the test widget.
 */
class AjaxTestController extends ActionController {

	/**
	 * Includes the widget through its Index.html template and renders it.
	 *
	 * @return string
	 */
	public function indexAction() {
	}

	/**
	 * Renders and returns the URI pointing to the widget for an AJAX call.
	 *
	 * @return string
	 */
	public function widgetUriAction() {
	}

}
?>