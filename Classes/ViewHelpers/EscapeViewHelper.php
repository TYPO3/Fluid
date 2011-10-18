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
 * The EscapeViewHelper is used to escape variable content in various ways. By
 * default HTML is the target.
 *
 * = Examples =
 *
 * <code title="HTML">
 * <f:escape>{text}</f:escape>
 * </code>
 * <output>
 * Text with & " ' < > * replaced by HTML entities (htmlspecialchars applied).
 * </output>
 *
 * <code title="Entities">
 * <f:escape type="entities">{text}</f:escape>
 * </code>
 * <output>
 * Text with all possible chars replaced by HTML entities (htmlentities applied).
 * </output>
 *
 * <code title="XML">
 * <f:escape type="xml">{text}</f:escape>
 * </code>
 * <output>
 * Replaces only "<", ">" and "&" by entities as required for valid XML.
 * </output>
 *
 * <code title="URL">
 * <f:escape type="url">{text}</f:escape>
 * </code>
 * <output>
 * Text encoded for URL use (rawurlencode applied).
 * </output>
 *
 * <code title="Text">
 * <f:escape type="text">{someHtml}</f:escape>
 * </code>
 * <output>
 * Strips all tags from the HTML or XML input and returns plain text.
 * </output>
 *
 * @api
 * @FLOW3\Scope("singleton")
 * @deprecated since 1.0.0 alpha 7; use corresponding f:format.* ViewHelpers instead
 */
class EscapeViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Disable the escaping interceptor because otherwise the child nodes would be escaped before this view helper
	 * can decode the text's entities.
	 *
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Escapes special characters with their escaped counterparts as needed.
	 *
	 * @param string $value
	 * @param string $type The type, one of html, entities, url
	 * @param string $encoding
	 * @return string the altered string.
	 * @api
	 */
	public function render($value = NULL, $type = 'html', $encoding = 'UTF-8') {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}

		if (!is_string($value)) {
			return $value;
		}

		switch ($type) {
			case 'html':
				return htmlspecialchars($value, ENT_COMPAT, $encoding);
			break;
			case 'entities':
				return htmlentities($value, ENT_COMPAT, $encoding);
			break;
			case 'xml':
				return htmlspecialchars($value, ENT_NOQUOTES, $encoding);
			break;
			case 'url':
				return rawurlencode($value);
			case 'text':
				return strip_tags($value);
			default:
				return $value;
			break;
		}
	}
}
?>