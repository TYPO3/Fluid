<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * If content is empty use alternative text
 */
class OrViewHelper extends AbstractViewHelper {

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('content', 'mixed', 'Content to check if empty', FALSE, '');
		$this->registerArgument('alternative', 'mixed', 'Alternative if content is empty', FALSE, '');
		$this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string, using sprintf', FALSE, NULL);
	}

	/**
	 * @return string
	 */
	public function render() {
		$content = $this->arguments['content'];
		$alternative = $this->arguments['alternative'];
		$arguments = (array) $this->arguments['arguments'];

		if (empty($arguments)) {
			$arguments = NULL;
		}

		if (NULL === $content) {
			$content = $this->renderChildren();
		}

		if (NULL === $content) {
			$content = $alternative;
		}

		if (FALSE === empty($content)) {
			$content = NULL !== $arguments ? vsprintf($content, $arguments) : $content;
		}

		return $content;
	}

}
