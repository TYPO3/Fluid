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

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException;

/**
 * Modifies the case of an input string to upper- or lowercase or capitalization.
 * The default transformation will be uppercase as in ``mb_convert_case`` [1].
 *
 * Possible modes are:
 *
 * ``lower``
 *   Transforms the input string to its lowercase representation
 *
 * ``upper``
 *   Transforms the input string to its uppercase representation
 *
 * ``capital``
 *   Transforms the input string to its first letter upper-cased, i.e. capitalization
 *
 * ``uncapital``
 *   Transforms the input string to its first letter lower-cased, i.e. uncapitalization
 *
 * ``capitalWords``
 *   Transforms the input string to each containing word being capitalized
 *
 * Note that the behavior will be the same as in the appropriate PHP function ``mb_convert_case`` [1];
 * especially regarding locale and multibyte behavior.
 *
 * @see http://php.net/manual/function.mb-convert-case.php [1]
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:format.case>Some Text with miXed case</f:format.case>
 * </code>
 * <output>
 * SOME TEXT WITH MIXED CASE
 * </output>
 *
 * <code title="Example with given mode">
 * <f:format.case mode="capital">someString</f:format.case>
 * </code>
 * <output>
 * SomeString
 * </output>
 *
 * <code title="Inline notation">
 * {article.title -> f:format.case(mode: 'capitalWords')}
 * </code>
 * <output>
 * Dolphins Vanish After A Surprisingly Sophisticated Attempt To Do A Double Backward Somersault
 * </output>
 *
 * @api
 */
class CaseViewHelper extends AbstractViewHelper {

	/**
	 * Directs the input string being converted to "lowercase"
	 */
	const CASE_LOWER = 'lower';

	/**
	 * Directs the input string being converted to "UPPERCASE"
	 */
	const CASE_UPPER = 'upper';

	/**
	 * Directs the input string being converted to "Capital case"
	 */
	const CASE_CAPITAL = 'capital';

	/**
	 * Directs the input string being converted to "unCapital case"
	 */
	const CASE_UNCAPITAL = 'uncapital';

	/**
	 * Directs the input string being converted to "Capital Case For Each Word"
	 */
	const CASE_CAPITAL_WORDS = 'capitalWords';

	/**
	 * Changes the case of the input string
	 *
	 * @param string $value The input value. If not given, the evaluated child nodes will be used
	 * @param string $mode The case to apply, must be one of this' CASE_* constants. Defaults to uppercase application
	 * @return string the altered string.
	 * @throws InvalidVariableException
	 * @api
	 */
	public function render($value = NULL, $mode = self::CASE_UPPER) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}

		$originalEncoding = mb_internal_encoding();
		mb_internal_encoding('UTF-8');

		switch ($mode) {
			case self::CASE_LOWER:
				$output = mb_convert_case($value, \MB_CASE_LOWER);
				break;
			case self::CASE_UPPER:
				$output = mb_convert_case($value, \MB_CASE_UPPER);
				break;
			case self::CASE_CAPITAL:
				$output = mb_substr(mb_convert_case($value, \MB_CASE_UPPER), 0, 1) . mb_substr($value, 1);
				break;
			case self::CASE_UNCAPITAL:
				$output = mb_substr(mb_convert_case($value, \MB_CASE_LOWER), 0, 1) . mb_substr($value, 1);
				break;
			case self::CASE_CAPITAL_WORDS:
				$output = mb_convert_case($value, \MB_CASE_TITLE);
				break;
			default:
				mb_internal_encoding($originalEncoding);
				throw new InvalidVariableException('The case mode "' . $mode . '" supplied to Fluid\'s format.case ViewHelper is not supported.', 1358349150);
		}

		mb_internal_encoding($originalEncoding);
		return $output;
	}
}
