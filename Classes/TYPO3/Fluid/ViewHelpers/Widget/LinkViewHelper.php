<?php
namespace TYPO3\Fluid\ViewHelpers\Widget;

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
use TYPO3\Flow\Security\Cryptography\HashService;
use TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\Fluid\Core\ViewHelper;
use TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException;
use TYPO3\Fluid\Core\Widget\WidgetContext;

/**
 * widget.link ViewHelper
 * This ViewHelper can be used inside widget templates in order to render links pointing to widget actions
 *
 * = Examples =
 *
 * <code>
 * <f:widget.link action="widgetAction" arguments="{foo: 'bar'}">some link</f:widget.link>
 * </code>
 * <output>
 *  <a href="--widget[@action]=widgetAction">some link</a>
 *  (depending on routing setup and current widget)
 * </output>
 *
 * @api
 */
class LinkViewHelper extends AbstractTagBasedViewHelper {

	/**
	 * @Flow\Inject
	 * @var HashService
	 */
	protected $hashService;

	/**
	 * @var string
	 */
	protected $tagName = 'a';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
		$this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked document');
		$this->registerTagAttribute('rev', 'string', 'Specifies the relationship between the linked document and the current document');
		$this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
	}

	/**
	 * Render the link.
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
			$uri = $this->getAjaxUri();
		} else {
			if ($action === NULL) {
				throw new ViewHelper\Exception('You have to specify the target action when creating a widget URI with the widget.link ViewHelper', 1357648227);
			}
			$uri = $this->getWidgetUri();
		}
		$this->tag->addAttribute('href', $uri);
		$this->tag->setContent($this->renderChildren());
		$this->tag->forceClosingTag(TRUE);

		return $this->tag->render();
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
			throw new WidgetContextNotFoundException('Widget context not found in <f:widget.link>', 1307450686);
		}
		if ($this->arguments['includeWidgetContext'] === TRUE) {
			$serializedWidgetContext = serialize($widgetContext);
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
