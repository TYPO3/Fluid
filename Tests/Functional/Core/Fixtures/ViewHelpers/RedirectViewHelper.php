<?php
namespace TYPO3\Fluid\Tests\Functional\Core\Fixtures\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
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
 * A view helper for the redirect test widget
 */
class RedirectViewHelper extends AbstractWidgetViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Fluid\Tests\Functional\Core\Fixtures\ViewHelpers\Controller\RedirectController
	 */
	protected $controller;

	/**
	 * The actual render method does nothing more than initiating the sub request
	 * which invokes the controller.
	 *
	 * @return string
	 */
	public function render() {
		return $this->initiateSubRequest();
	}
}
?>