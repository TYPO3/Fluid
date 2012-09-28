<?php
namespace TYPO3\Fluid\Core\Widget;

/*
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This is the base class for all widget controllers.
 * Basically, it is an ActionController, and it additionally
 * has $this->widgetConfiguration set to the Configuration of the current Widget.
 *
 * @api
 */
abstract class AbstractWidgetController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
	 * @param \TYPO3\Flow\Mvc\ActionRequest $request The request object
	 * @param \TYPO3\Flow\Http\Response $response The response, modified by this handler
	 * @return void
	 * @throws \TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException
	 * @api
	 */
	public function processRequest(\TYPO3\Flow\Mvc\RequestInterface $request, \TYPO3\Flow\Mvc\ResponseInterface $response) {
		$widgetContext = $request->getInternalArgument('__widgetContext');
		if ($widgetContext === NULL) {
			throw new \TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException('The widget context could not be found in the request.', 1307450180);
		}
		$this->widgetConfiguration = $widgetContext->getWidgetConfiguration();
		parent::processRequest($request, $response);
	}

}

?>