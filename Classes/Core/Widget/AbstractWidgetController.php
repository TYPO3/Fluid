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
 * This is the base class for all widget controllers.
 * Basically, it is an ActionController, and it additionally
 * has $this->widgetConfiguration set to the Configuration of the current Widget.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
abstract class AbstractWidgetController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * Configuration for this widget.
	 *
	 * @var array
	 * @api
	 */
	protected $widgetConfiguration;

	/**
	 * Handles a request. The result output is returned by altering the given response.
	 *
	 * @param \TYPO3\FLOW3\MVC\RequestInterface $request The request object
	 * @param \TYPO3\FLOW3\MVC\ResponseInterface $response The response, modified by this handler
	 * @return void
	 * @api
	 */
	public function processRequest(\TYPO3\FLOW3\MVC\RequestInterface $request, \TYPO3\FLOW3\MVC\ResponseInterface $response) {
		$widgetContext = $request->getInternalArgument('__widgetContext');
		if ($widgetContext === NULL) {
			throw new \TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException('The widget context could not be found in the request.', 1307450180);
		}
		$this->widgetConfiguration = $widgetContext->getWidgetConfiguration();
		parent::processRequest($request, $response);
	}

}

?>