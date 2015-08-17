<?php
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Interface TemplateProcessorInterface
 *
 * Implemented by classes that process template
 * sources before they are handed off to the
 * TemplateParser. Allows classes to manipulate
 * the template source, the TemplateParser and
 * the ViewHelperResolver through public API.
 *
 * For example, allowing an implementer to extract
 * custom instructions from the template which are
 * then used to manipulate how ViewHelpers resolve.
 */
interface TemplateProcessorInterface {

	/**
	 * Setter for passing the TemplateParser instance
	 * that is currently processing the template.
	 *
	 * @param TemplateParser $templateParser
	 * @return void
	 */
	public function setTemplateParser(TemplateParser $templateParser);

	/**
	 * Setter for passing the ViewHelperResolver instance
	 * being used by the TemplateParser to resolve classes
	 * and namespaces of ViewHelpers.
	 *
	 * @param ViewHelperResolver $viewHelperResolver
	 * @return void
	 */
	public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver);

	/**
	 * Pre-process the template source before it is
	 * returned to the TemplateParser or passed to
	 * the next TemplateProcessorInterface instance.
	 *
	 * @param string $templateSource
	 * @return string
	 */
	public function preProcessSource($templateSource);

}
