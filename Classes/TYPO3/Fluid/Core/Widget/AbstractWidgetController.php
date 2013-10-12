<?php
namespace TYPO3\Fluid\Core\Widget;

/*
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\RequestInterface;
use TYPO3\Flow\Mvc\ResponseInterface;
use TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException;

/**
 * This is the base class for all widget controllers.
 * Basically, it is an ActionController, and it additionally
 * has $this->widgetConfiguration set to the Configuration of the current Widget.
 *
 * @api
 */
abstract class AbstractWidgetController extends ActionController {

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
	 * @param RequestInterface $request The request object
	 * @param ResponseInterface $response The response, modified by this handler
	 * @return void
	 * @throws WidgetContextNotFoundException
	 * @api
	 */
	public function processRequest(RequestInterface $request, ResponseInterface $response) {
		/** @var $request \TYPO3\Flow\Mvc\ActionRequest */
		/** @var $widgetContext WidgetContext */
		$widgetContext = $request->getInternalArgument('__widgetContext');
		if ($widgetContext === NULL) {
			throw new WidgetContextNotFoundException('The widget context could not be found in the request.', 1307450180);
		}
		$this->widgetConfiguration = $widgetContext->getWidgetConfiguration();
		parent::processRequest($request, $response);
	}

}
