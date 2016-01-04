<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A view helper for formatting values with printf. Either supply an array for
 * the arguments or a single value.
 * See http://www.php.net/manual/en/function.sprintf.php
 *
 * ### Examples
 *
 * ##### Scientific notation
 *
 * ```html
 * <f:format.printf arguments="{number: 362525200}">%.3e</f:format.printf>
 * ```
 * will output ```3.625e+8```
 *
 * ##### Argument swapping
 *
 * ```html
 * <f:format.printf arguments="{0: 3, 1: 'Kasper'}">%2$s is great, TYPO%1$d too. Yes, TYPO%1$d is great and so is %2$s!</f:format.printf>
 * ```
 * will output ```Kasper is great, TYPO3 too. Yes, TYPO3 is great and so is Kasper!```
 *
 * ##### Single argument
 *
 * ```html
 * <f:format.printf arguments="{1: 'TYPO3'}">We love %s</f:format.printf>
 * ```
 * will output ```We love TYPO3```
 *
 * ##### Inline notation
 *
 * ```html
 * {someText -> f:format.printf(arguments: {1: 'TYPO3'})}
 * ```
 * will output ```We love TYPO3```
 *
 * @api
 */
class PrintfViewHelper extends AbstractViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('arguments', 'array', 'The arguments for vsprintf', FALSE, array());
		$this->registerArgument('value', 'string', 'String to format', FALSE, FALSE);
	}

	/**
	 * Format the arguments with the given printf format string.
	 *
	 * @return string The formatted value
	 * @api
	 */
	public function render() {
		return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * Applies vsprintf() on the specified value.
	 *
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return string
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$value = $arguments['value'];
		if ($value === NULL) {
			$value = $renderChildrenClosure();
		}

		return vsprintf($value, $arguments['arguments']);
	}
}
