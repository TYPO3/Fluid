<?php
namespace TYPO3\Fluid\ViewHelpers\Widget;

/*
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Cryptography\HashService;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper;
use TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException;
use TYPO3\Fluid\Core\Widget\WidgetContext;

/**
 * widget.uri ViewHelper
 * This ViewHelper can be used inside widget templates in order to render URIs pointing to widget actions
 *
 * = Examples =
 *
 * <code>
 * {f:widget.uri(action: 'widgetAction')}
 * </code>
 * <output>
 *  --widget[@action]=widgetAction
 *  (depending on routing setup and current widget)
 * </output>
 *
 * @api
 */
class UriViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var HashService
	 */
	protected $hashService;

	/**
	 * Render the Uri.
	 *
	 * @param string $action Target action
	 * @param array $arguments Arguments
	 * @param string $section The anchor to be added to the URI
	 * @param string $format The requested format, e.g. ".html"
	 * @param boolean $ajax TRUE if the URI should be to an AJAX widget, FALSE otherwise.
	 * @param boolean $includeWidgetContext TRUE if the URI should contain the serialized widget context (only useful for stateless AJAX widgets)
	 * @return string The rendered link
	 * @throws ViewHelper\Exception if $action argument is not specified and $ajax is FALSE
	 * @api
	 */
	public function render($action = NULL, $arguments = array(), $section = '', $format = '', $ajax = FALSE, $includeWidgetContext = FALSE) {
		if ($ajax === TRUE) {
			return $this->getAjaxUri();
		} else {
			if ($action === NULL) {
				throw new ViewHelper\Exception('You have to specify the target action when creating a widget URI with the widget.uri ViewHelper', 1357648232);
			}
			return $this->getWidgetUri();
		}
	}

	/**
	 * Get the URI for an AJAX Request.
	 *
	 * @return string the AJAX URI
	 * @throws WidgetContextNotFoundException
	 */
	protected function getAjaxUri() {
		$action = $this->arguments['action'];
		$arguments = $this->arguments['arguments'];

		if ($action === NULL) {
			$action = $this->controllerContext->getRequest()->getControllerActionName();
		}
		$arguments['@action'] = $action;
		if (strlen($this->arguments['format']) > 0) {
			$arguments['@format'] = $this->arguments['format'];
		}
		/** @var $widgetContext WidgetContext */
		$widgetContext = $this->controllerContext->getRequest()->getInternalArgument('__widgetContext');
		if ($widgetContext === NULL) {
			throw new WidgetContextNotFoundException('Widget context not found in <f:widget.uri>', 1307450639);
		}
		if ($this->arguments['includeWidgetContext'] === TRUE) {
			$serializedWidgetContext = base64_encode(serialize($widgetContext));
			$arguments['__widgetContext'] = $this->hashService->appendHmac($serializedWidgetContext);
		} else {
			$arguments['__widgetId'] = $widgetContext->getAjaxWidgetIdentifier();
		}
		return '?' . http_build_query($arguments, NULL, '&');
	}

	/**
	 * Get the URI for a non-AJAX Request.
	 *
	 * @return string the Widget URI
	 * @throws ViewHelper\Exception
	 * @todo argumentsToBeExcludedFromQueryString does not work yet, needs to be fixed.
	 */
	protected function getWidgetUri() {
		$uriBuilder = $this->controllerContext->getUriBuilder();

		$argumentsToBeExcludedFromQueryString = array(
			'@package',
			'@subpackage',
			'@controller'
		);

		$uriBuilder
			->reset()
			->setSection($this->arguments['section'])
			->setCreateAbsoluteUri(TRUE)
			->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
			->setFormat($this->arguments['format']);
		try {
			$uri = $uriBuilder->uriFor($this->arguments['action'], $this->arguments['arguments'], '', '', '');
		} catch (\Exception $exception) {
			throw new ViewHelper\Exception($exception->getMessage(), $exception->getCode(), $exception);
		}
		return $uri;
	}
}
