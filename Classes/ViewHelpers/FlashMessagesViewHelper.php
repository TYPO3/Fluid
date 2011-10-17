<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * View helper which renders the flash messages (if there are any) as an unsorted list.
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:flashMessages />
 * </code>
 * <output>
 * <ul>
 *   <li class="flashmessages-ok">Some Default Message</li>
 *   <li class="flashmessages-warning">Some Warning Message</li>
 * </ul>
 * </output>
 * Depending on the FlashMessages
 *
 * <code title="Output with css class">
 * <f:flashMessages class="specialClass" />
 * </code>
 * <output>
 * <ul class="specialClass">
 *   <li class="specialClass-ok">Default Message</li>
 *   <li class="specialClass-notice"><h3>Some notice message</h3>With message title</li>
 * </ul>
 * </output>
 *
 * <code title="Output flash messages as a list, with arguments and filtered by a severity">
 * <f:flashMessages severity="Warning" as="flashMessages">
 * 	<dl class="messages">
 * 	<f:for each="{flashMessages}" as="flashMessage">
 * 		<dt>{flashMessage.code}</dt>
 * 		<dd>{flashMessage}</dd>
 * 	</f:for>
 * 	</dl>
 * </f:flashMessages>
 * </code>
 * <output>
 * <dl class="messages">
 * 	<dt>1013</dt>
 * 	<dd>Some Warning Message.</dd>
 * </dl>
 * </output>
 *
 * @api
 */
class FlashMessagesViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'ul';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders flash messages that have been added to the FlashMessageContainer in previous request(s).
	 *
	 * @param string $as The name of the current flashMessage variable for rendering inside
	 * @param string $severity severity of the messages (One of the \TYPO3\FLOW3\Error\Message::SEVERITY_* constants)
	 * @return string rendered Flash Messages, if there are any.
	 * @api
	 */
	public function render($as = NULL, $severity = NULL) {
		$flashMessages = $this->controllerContext->getFlashMessageContainer()->getMessagesAndFlush($severity);
		if (count($flashMessages) < 1) {
			return '';
		}
		if ($as === NULL) {
			$content = $this->renderAsList($flashMessages);
		} else {
			$content = $this->renderFromTemplate($flashMessages, $as);
		}
		return $content;
	}

	/**
	 * Render the flash messages as unsorted list. This is triggered if no "as" argument is given
	 * to the ViewHelper.
	 *
	 * @param array $flashMessages
	 * @return string
	 */
	protected function renderAsList(array $flashMessages) {
		$flashMessagesClass = $this->arguments['class'] !== NULL ? $this->arguments['class'] : 'flashmessages';
		$tagContent = '';
		foreach ($flashMessages as $singleFlashMessage) {
			$severityClass = sprintf('%s-%s', $flashMessagesClass, strtolower($singleFlashMessage->getSeverity()));
			$messageContent = htmlspecialchars($singleFlashMessage->render());
			if ($singleFlashMessage->getTitle() !== '') {
				$messageContent = sprintf('<h3>%s</h3>', htmlspecialchars($singleFlashMessage->getTitle())) . $messageContent;
			}
			$tagContent .= sprintf('<li class="%s">%s</li>', htmlspecialchars($severityClass), $messageContent);
		}
		$this->tag->setContent($tagContent);
		$content = $this->tag->render();

		return $content;
	}

	/**
	 * Defer the rendering of Flash Messages to the template. In this case,
	 * the flash messages are stored in the template inside the variable specified
	 * in "as".
	 *
	 * @param array $flashMessages
	 * @param string $as
	 * @return string
	 */
	protected function renderFromTemplate(array $flashMessages, $as) {
		$templateVariableContainer = $this->renderingContext->getTemplateVariableContainer();
		$templateVariableContainer->add($as, $flashMessages);
		$content = $this->renderChildren();
		$templateVariableContainer->remove($as);

		return $content;
	}
}
?>