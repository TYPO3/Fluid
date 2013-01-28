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
 * Encodes the given string according to http://www.faqs.org/rfcs/rfc3986.html (applying PHPs rawurlencode() function)
 * @see http://www.php.net/manual/function.urlencode.php
 *
 * = Examples =
 *
 * <code title="default notation">
 * <f:format.urlencode>foo @+%/</f:format.urlencode>
 * </code>
 * <output>
 * foo%20%40%2B%25%2F (rawurlencode() applied)
 * </output>
 *
 * <code title="inline notation">
 * {text -> f:format.urlencode()}
 * </code>
 * <output>
 * Url encoded text (rawurlencode() applied)
 * </output>
 *
 * @api
 */
class UrlencodeViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Disable the escaping interceptor because otherwise the child nodes would be escaped before this view helper
	 * can decode the text's entities.
	 *
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Escapes special characters with their escaped counterparts as needed using PHPs urlencode() function.
	 *
	 * @param string $value string to format
	 * @return mixed
	 * @see http://www.php.net/manual/function.urlencode.php
	 * @api
	 * @throws \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function render($value = NULL) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}
		if (is_string($value) || (is_object($value) && method_exists($value, '__toString')) ) {
			return rawurlencode($value);
		}

		throw new \TYPO3\Fluid\Core\ViewHelper\Exception('This ViewHelper works with values that are of type string or objects that implement a __toString method. You provided "' . is_object($value) ? get_class($value) : gettype($value) . '"', 1359389241);
	}

}
?>