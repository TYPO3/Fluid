<?php
namespace TYPO3\Fluid\Tests\Functional\Core\Fixtures\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * A view helper for the test AJAX widget
 */
class SomeAjaxViewHelper extends AbstractWidgetViewHelper {

	/**
	 * @var boolean
	 */
	protected $ajaxWidget = TRUE;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Fluid\Tests\Functional\Core\Fixtures\ViewHelpers\Controller\SomeAjaxController
	 */
	protected $controller;

	/**
	 * The actual render method does nothing more than initiating the sub request
	 * which invokes the controller.
	 *
	 * @param string $option1 Option for testing if parameters can be passed
	 * @param string $option2 Option for testing if parameters can be passed
	 * @return string
	 */
	public function render($option1 = '', $option2 = '') {
		return $this->initiateSubRequest();
	}
}
?>