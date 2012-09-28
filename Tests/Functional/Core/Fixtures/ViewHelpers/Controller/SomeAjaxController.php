<?php
namespace TYPO3\Fluid\Tests\Functional\Core\Fixtures\ViewHelpers\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\Widget\AbstractWidgetController;

/**
 * Controller of the test AJAX widget
 */
class SomeAjaxController extends AbstractWidgetController {

	/**
	 * The default action which is invoked when the widget is rendered as part of a
	 * Fluid template.
	 *
	 * The template of this action renders an OK string and the URI pointing to the
	 * ajaxAction().
	 *
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * An action which is supposed to be invoked through AJAX
	 *
	 * @return string
	 */
	public function ajaxAction() {
		$options = (isset($this->widgetConfiguration['option1']) ? '"' . $this->widgetConfiguration['option1'] . '"' : '""') . ', ';
		$options .= (isset($this->widgetConfiguration['option2']) ? '"' . $this->widgetConfiguration['option2'] . '"' : '""') . '';
		return sprintf('SomeAjaxController::ajaxAction(%s)', $options);
	}

}
?>