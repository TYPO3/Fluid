<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * An interface all view helpers must implement.
 *
 * @internal You may type hint this interface, but you should always
 *           extend AbstractViewHelper or some other abstract that
 *           extends AbstractViewHelper with own view helper
 *           implementations.
 *           This interface currently only ships internal Fluid API,
 *           which may change. Those methods are "correctly" implemented
 *           in the AbstractViewHelper and maintained.
 *           We'll try to resolve this restriction midterm, but you should
 *           not fully implement ViewHelperInterface yourself for now.
 */
interface ViewHelperInterface
{
    /**
     * @return ArgumentDefinition[]
     */
    public function prepareArguments(): array;

    /**
     * @param array<string, mixed> $arguments
     */
    public function setArguments(array $arguments): void;

    public function getContentArgumentName(): ?string;

    public function setViewHelperNode(ViewHelperNode $node): void;

    /**
     * @param RenderingContextInterface $renderingContext
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext): void;

    /**
     * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
     *
     * @return mixed the rendered ViewHelper.
     */
    public function initializeArgumentsAndRender(): mixed;

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array<string, mixed> $arguments
     */
    public function handleAdditionalArguments(array $arguments): void;

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array<string, mixed> $arguments
     */
    public function validateAdditionalArguments(array $arguments): void;

    /**
     * Called when being inside a cached template.
     */
    public function setRenderChildrenClosure(\Closure $renderChildrenClosure): void;

    /**
     * Main method called at compile time to turn this ViewHelper
     * into a PHP representation written to compiled templates cache.
     *
     * This method is a layer above / earlier than compile() and returns
     * an array with identical structure as NodeInterface::convert().
     *
     * This method is considered Fluid internal, own view helpers should
     * refrain from overriding this. Overriding this method is typically
     * only needed when the compiled template code needs to be optimized
     * in a way compile() does not allow.
     *
     * There are some caveats when overriding this method: First, this
     * is not supported territory. Second, this may give additional
     * headaches when a VH with this method "overrides" an existing
     * VH via namespace declaration, since this adds a runtime dependency
     * to compile time. Don't do it.
     *
     * @internal Do not override except you know exactly what you are doing.
     *           Be prepared to maintain this in the future, it may break any time.
     *           Also, both method signature and return array structure may change any time.
     * @return array{initialization: string, execution: string}
     */
    public function convert(TemplateCompiler $templateCompiler): array;

    public function isChildrenEscapingEnabled(): bool;

    public function isOutputEscapingEnabled(): bool;
}
