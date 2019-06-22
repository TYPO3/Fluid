<?php
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * Abstract Fluid Compiled template.
 *
 * INTERNAL!!
 */
abstract class AbstractCompiledTemplate implements ParsedTemplateInterface
{

    /**
     * @param string $identifier
     * @return void
     */
    public function setIdentifier(string $identifier): void
    {
        // void, ignored.
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return static::class;
    }

    /**
     * Returns a variable container used in the PostParse Facet.
     *
     * @return VariableProviderInterface
     */
    public function getVariableContainer(): VariableProviderInterface
    {
        return new StandardVariableProvider();
    }

    /**
     * Render the parsed template with rendering context
     *
     * @param RenderingContextInterface $renderingContext The rendering context to use
     * @return mixed Rendered string
     */
    public function render(RenderingContextInterface $renderingContext)
    {
        return '';
    }

    /**
     * @return boolean
     */
    public function isCompilable(): bool
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isCompiled(): bool
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function hasLayout(): bool
    {
        return false;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public function getLayoutName(RenderingContextInterface $renderingContext): string
    {
        return '';
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function addCompiledNamespaces(RenderingContextInterface $renderingContext): void
    {
    }
}
