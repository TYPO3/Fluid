<?php
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

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
    public function setIdentifier($identifier)
    {
        // void, ignored.
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return static::class;
    }

    /**
     * Returns a variable container used in the PostParse Facet.
     *
     * @return VariableProviderInterface
     */
    public function getVariableContainer()
    {
        return new StandardVariableProvider();
    }

    /**
     * Render the parsed template with rendering context
     *
     * @param RenderingContextInterface $renderingContext The rendering context to use
     * @return string Rendered string
     */
    public function render(RenderingContextInterface $renderingContext)
    {
        return '';
    }

    /**
     * @return boolean
     */
    public function isCompilable()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isCompiled()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function hasLayout()
    {
        return false;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public function getLayoutName(RenderingContextInterface $renderingContext)
    {
        return '';
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function addCompiledNamespaces(RenderingContextInterface $renderingContext)
    {
    }
}
