<?php
namespace TYPO3\Fluid\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Wrapper for PHPs json_encode function.
 *
 * = Examples =
 *
 * <code title="encoding a view variable">
 * {someArray -> f:format.json()}
 * </code>
 * <output>
 * ["array","values"]
 * // depending on the value of {someArray}
 * </output>
 *
 * <code title="associative array">
 * {f:format.json(value: {foo: 'bar', bar: 'baz'})}
 * </code>
 * <output>
 * {"foo":"bar","bar":"baz"}
 * </output>
 *
 * <code title="non-associative array with forced object">
 * {f:format.json(value: {0: 'bar', 1: 'baz'}, forceObject: 1)}
 * </code>
 * <output>
 * {"0":"bar","1":"baz"}
 * </output>
 *
 * @api
 */
class JsonViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Outputs content with its JSON representation. To prevent issues in HTML context, occurrences
	 * of greater-than or less-than characters are converted to their hexadecimal representations.
	 *
	 * If $forceObject is TRUE a JSON object is outputted even if the value is a non-associative array
	 * Example: array('foo', 'bar') as input will not be ["foo","bar"] but {"0":"foo","1":"bar"}
	 *
	 * @param mixed $value The incoming data to convert, or NULL if VH children should be used
	 * @param boolean $forceObject Outputs an JSON object rather than an array
	 * @return string the JSON-encoded string.
	 * @see http://www.php.net/manual/en/function.json-encode.php
	 * @api
	 */
	public function render($value = NULL, $forceObject = FALSE) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}
		$options = JSON_HEX_TAG;
		if ($forceObject !== FALSE) {
			$options = $options | JSON_FORCE_OBJECT;
		}
		return json_encode($value, $options);
	}
}
?>