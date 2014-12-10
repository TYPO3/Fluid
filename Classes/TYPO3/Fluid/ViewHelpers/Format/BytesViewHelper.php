<?php
namespace TYPO3\Fluid\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Utility\Files;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Formats an integer with a byte count into human-readable form.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * {fileSize -> f:format.bytes()}
 * </code>
 * <output>
 * 123 KB
 * // depending on the value of {fileSize}
 * </output>
 *
 * <code title="Defaults">
 * {fileSize -> f:format.bytes(decimals: 2, decimalSeparator: ',', thousandsSeparator: ',')}
 * </code>
 * <output>
 * 1,023.00 B
 * // depending on the value of {fileSize}
 * </output>
 *
 * @api
 */
class BytesViewHelper extends AbstractViewHelper implements CompilableInterface {

	/**
	 * Render the supplied byte count as a human readable string.
	 *
	 * @param integer $value The incoming data to convert, or NULL if VH children should be used
	 * @param integer $decimals The number of digits after the decimal point
	 * @param string $decimalSeparator The decimal point character
	 * @param string $thousandsSeparator The character for grouping the thousand digits
	 * @return string Formatted byte count
	 * @api
	 */
	public function render($value = NULL, $decimals = 0, $decimalSeparator = '.', $thousandsSeparator = ',') {
		return self::renderStatic(array('value' => $value, 'decimals' => $decimals, 'decimalSeparator' => $decimalSeparator, 'thousandsSeparator' => $thousandsSeparator), $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * Applies htmlspecialchars() on the specified value.
	 *
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return string
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$value = $arguments['value'];
		if ($value === NULL) {
			$value = $renderChildrenClosure();
		}
		if (!is_integer($value) && !is_float($value)) {
			if (is_numeric($value)) {
				$value = (float)$value;
			} else {
				$value = 0;
			}
		}
		return Files::bytesToSizeString($value, $arguments['decimals'], $arguments['decimalSeparator'], $arguments['thousandsSeparator']);
	}
}
