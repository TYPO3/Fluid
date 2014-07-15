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

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Cldr\Reader\NumbersReader;
use TYPO3\Flow\I18n\Exception as I18nException;
use TYPO3\Flow\I18n\Formatter\NumberFormatter;
use TYPO3\Fluid\Core\ViewHelper\AbstractLocaleAwareViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Exception as ViewHelperException;

/**
 * Formats a number with custom precision, decimal point and grouped thousands.
 * @see http://www.php.net/manual/en/function.number-format.php
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <f:format.number>423423.234</f:format.number>
 * </code>
 * <output>
 * 423,423.20
 * </output>
 *
 * <code title="With all parameters">
 * <f:format.number decimals="1" decimalSeparator="," thousandsSeparator=".">423423.234</f:format.number>
 * </code>
 * <output>
 * 423.423,2
 * </output>
 *
 * <code title="Inline notation with current locale used">
 * {someNumber -> f:format.number(forceLocale: true)}
 * </code>
 * <output>
 * 54.321,00
 * (depending on the value of {someNumber} and the current locale)
 * </output>
 *
 * <code title="Inline notation with specific locale used">
 * {someNumber -> f:format.currency(forceLocale: 'de_DE')}
 * </code>
 * <output>
 * 54.321,00
 * (depending on the value of {someNumber})
 * </output>
 *
 * @api
 */
class NumberViewHelper extends AbstractLocaleAwareViewHelper {

	/**
	 * @Flow\Inject
	 * @var NumberFormatter
	 */
	protected $numberFormatter;

	/**
	 * Format the numeric value as a number with grouped thousands, decimal point and
	 * precision.
	 *
	 * @param int $decimals The number of digits after the decimal point
	 * @param string $decimalSeparator The decimal point character
	 * @param string $thousandsSeparator The character for grouping the thousand digits
	 * @param string $localeFormatLength Format length if locale set in $forceLocale. Must be one of TYPO3\Flow\I18n\Cldr\Reader\NumbersReader::FORMAT_LENGTH_*'s constants.
	 * @return string The formatted number
	 * @api
	 * @throws ViewHelperException
	 */
	public function render($decimals = 2, $decimalSeparator = '.', $thousandsSeparator = ',', $localeFormatLength = NumbersReader::FORMAT_LENGTH_DEFAULT) {
		$stringToFormat = $this->renderChildren();

		$useLocale = $this->getLocale();
		if ($useLocale !== NULL) {
			try {
				$output = $this->numberFormatter->formatDecimalNumber($stringToFormat, $useLocale, $localeFormatLength);
			} catch (I18nException $exception) {
				throw new ViewHelperException($exception->getMessage(), 1382351148, $exception);
			}
		} else {
			$output = number_format((float)$stringToFormat, $decimals, $decimalSeparator, $thousandsSeparator);
		}
		return $output;
	}
}
