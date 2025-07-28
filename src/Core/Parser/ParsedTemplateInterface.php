<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

/**
 * This interface is returned by \TYPO3Fluid\Fluid\Core\Parser\TemplateParser->parse()
 * method and is a parsed template
 *
 * @internal This interface should be used for type-checks only.
 */
interface ParsedTemplateInterface
{
    public function setIdentifier(string $identifier);

    public function getIdentifier(): string;

    /**
     * @return ArgumentDefinition[]
     */
    public function getArgumentDefinitions(): array;

    /**
     * @return string[]
     */
    public function getAvailableSlots(): array;

    /**
     * Render the parsed template with rendering context
     *
     * @param RenderingContextInterface $renderingContext The rendering context to use
     */
    public function render(RenderingContextInterface $renderingContext): mixed;

    /**
     * Returns a variable container used in the PostParse Facet.
     */
    public function getVariableContainer(): VariableProviderInterface;

    /**
     * Returns the name of the layout that is defined within the current template via <f:layout name="..." />
     * If no layout is defined, this returns null.
     * This requires the current rendering context in order to be able to evaluate the layout name
     *
     * @todo remove NodeInterface from return types in Fluid v5
     */
    public function getLayoutName(RenderingContextInterface $renderingContext): string|null|NodeInterface;

    /**
     * Method generated on compiled templates to add ViewHelper namespaces which were defined in-template
     * and add those to the ones already defined in the ViewHelperResolver.
     */
    public function addCompiledNamespaces(RenderingContextInterface $renderingContext): void;

    /**
     * Returns true if the current template has a template defined via <f:layout name="..." />
     */
    public function hasLayout(): bool;

    /**
     * If the template contains constructs which prevent the compiler from compiling the template
     * correctly, isCompilable() will return false.
     *
     * @return bool true if the template can be compiled
     */
    public function isCompilable(): bool;

    /**
     * @return bool true if the template is already compiled
     */
    public function isCompiled(): bool;
}
