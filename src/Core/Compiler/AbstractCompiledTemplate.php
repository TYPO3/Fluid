<?php
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Abstract Fluid Compiled template.
 *
 * INTERNAL!!
 */
abstract class AbstractCompiledTemplate implements ParsedTemplateInterface {

	/**
	 * Returns a variable container used in the PostParse Facet.
	 *
	 * @return VariableProviderInterface
	 */
	public function getVariableContainer() {
		return new StandardVariableProvider();
	}

	/**
	 * Render the parsed template with rendering context
	 *
	 * @param RenderingContextInterface $renderingContext The rendering context to use
	 * @return string Rendered string
	 */
	public function render(RenderingContextInterface $renderingContext) {
		return '';
	}

	/**
	 * Public such that it is callable from within closures
	 *
	 * @param integer $uniqueCounter
	 * @param RenderingContextInterface $renderingContext
	 * @param string $viewHelperName
	 * @return AbstractViewHelper
	 */
	public function getViewHelper($uniqueCounter, RenderingContextInterface $renderingContext, $viewHelperName) {
		return $renderingContext->getViewHelperResolver()->createViewHelperInstanceFromClassName($viewHelperName);
	}

	/**
	 * @return boolean
	 */
	public function isCompilable() {
		return FALSE;
	}

	/**
	 * @return boolean
	 */
	public function isCompiled() {
		return TRUE;
	}

	/**
	 * @return boolean
	 */
	public function hasLayout() {
		return FALSE;
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	public function getLayoutName(RenderingContextInterface $renderingContext) {
		return '';
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @return void
	 */
	public function addCompiledNamespaces(RenderingContextInterface $renderingContext) {
	}

}
