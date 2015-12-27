<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Outputs an argument/value without any escaping and wraps it with CDATA tags.
 *
 * PAY SPECIAL ATTENTION TO SECURITY HERE (especially Cross Site Scripting),
 * as the output is NOT SANITIZED!
 *
 * = Examples =
 *
 * <code title="Child nodes">
 * <f:format.cdata>{string}</f:format.cdata>
 * </code>
 * <output>
 * <![CDATA[(Content of {string} without any conversion/escaping)]]>
 * </output>
 *
 * <code title="Value attribute">
 * <f:format.cdata value="{string}" />
 * </code>
 * <output>
 * <![CDATA[(Content of {string} without any conversion/escaping)]]>
 * </output>
 *
 * <code title="Inline notation">
 * {string -> f:format.cdata()}
 * </code>
 * <output>
 * <![CDATA[(Content of {string} without any conversion/escaping)]]>
 * </output>
 *
 * @api
 */
class CdataViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeChildren = FALSE;

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('value', 'mixed', 'The value to output', FALSE, NULL);
	}
	/**
	 * @return string
	 */
	public function render() {
		return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param callable $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	public static function renderStatic(
		array $arguments,
		\Closure $renderChildrenClosure,
		RenderingContextInterface $renderingContext
	) {
		return sprintf('<![CDATA[%s]]>', isset($arguments['value']) ? $arguments['value'] : $renderChildrenClosure());
	}

}
