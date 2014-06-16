<?php
namespace TYPO3\Fluid\Tests\Functional\Core\Fixtures\ViewHelpers\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\Widget\AbstractWidgetController;

/**
 * Controller of the redirect widget
 */
class RedirectController extends AbstractWidgetController {

	/**
	 * Initial action (showing different links)
	 *
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * The target action for redirects/forwards
	 *
	 * @param string $parameter
	 * @return void
	 */
	public function targetAction($parameter = NULL) {
		$this->view->assign('parameter', $parameter);
	}

	/**
	 * @param integer $delay
	 * @param string $parameter
	 * @param boolean $otherController
	 * @return void
	 */
	public function redirectTestAction($delay = 0, $parameter = NULL, $otherController = FALSE) {
		$this->addFlashMessage('Redirection triggered!');
		$arguments = array();
		if ($parameter !== NULL) {
			$arguments['parameter'] = $parameter . ', via redirect';
		}
		$action = $otherController ? 'index' : 'target';
		$controller = $otherController ? 'Paginate' : NULL;
		$package = $otherController ? 'TYPO3.Fluid\ViewHelpers\Widget' : NULL;
		$this->redirect($action, $controller, $package, $arguments, $delay);
	}

	/**
	 * @param string $parameter
	 * @param boolean $otherController
	 * @return void
	 */
	public function forwardTestAction($parameter = NULL, $otherController = FALSE) {
		$this->addFlashMessage('Forward triggered!');
		$arguments = array();
		if ($parameter !== NULL) {
			$arguments['parameter'] = $parameter . ', via forward';
		}
		$action = $otherController ? 'index' : 'target';
		$controller = $otherController ? 'Standard' : NULL;
		$package = $otherController ? 'TYPO3.Flow\Mvc' : NULL;
		$this->forward($action, $controller, $package, $arguments);
	}

}
?>