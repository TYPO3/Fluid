<?php
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * This interface is returned by \TYPO3Fluid\Fluid\Core\Parser\TemplateParser->parse()
 * method and is a parsed template
 */
interface ParsedTemplateInterface
{

    /**
     * @param string $identifier
     * @return void
     */
    public function setIdentifier(string $identifier): void;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Render the parsed template with rendering context
     *
     * @param RenderingContextInterface $renderingContext The rendering context to use
     * @return mixed Rendered string
     */
    public function render(RenderingContextInterface $renderingContext);

    /**
     * Returns a variable container used in the PostParse Facet.
     *
     * @return VariableProviderInterface
     */
    public function getVariableContainer(): VariableProviderInterface;

    /**
     * Returns the name of the layout that is defined within the current template via <f:layout name="..." />
     * If no layout is defined, this returns NULL
     * This requires the current rendering context in order to be able to evaluate the layout name
     *
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public function getLayoutName(RenderingContextInterface $renderingContext): string;

    /**
     * Method generated on compiled templates to add ViewHelper namespaces which were defined in-template
     * and add those to the ones already defined in the ViewHelperResolver.
     *
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function addCompiledNamespaces(RenderingContextInterface $renderingContext): void;

    /**
     * Returns TRUE if the current template has a template defined via <f:layout name="..." />
     *
     * @return boolean
     */
    public function hasLayout(): bool;

    /**
     * If the template contains constructs which prevent the compiler from compiling the template
     * correctly, isCompilable() will return FALSE.
     *
     * @return boolean TRUE if the template can be compiled
     */
    public function isCompilable(): bool;

    /**
     * @return boolean TRUE if the template is already compiled, FALSE otherwise
     */
    public function isCompiled(): bool;
}
