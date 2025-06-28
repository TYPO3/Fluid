<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * Abstract Fluid Compiled template.
 *
 * @internal
 */
abstract class AbstractCompiledTemplate implements ParsedTemplateInterface
{
    public function setIdentifier(string $identifier): void
    {
        // void, ignored.
    }

    public function getIdentifier(): string
    {
        return static::class;
    }

    public function getVariableContainer(): VariableProviderInterface
    {
        return new StandardVariableProvider();
    }

    public function getArgumentDefinitions(): array
    {
        return [];
    }

    public function getAvailableSlots(): array
    {
        return [];
    }

    /**
     * Render the parsed template with rendering context
     *
     * @param RenderingContextInterface $renderingContext The rendering context to use
     * @return string Rendered string
     */
    public function render(RenderingContextInterface $renderingContext): mixed
    {
        return '';
    }

    public function isCompilable(): bool
    {
        return false;
    }

    public function isCompiled(): bool
    {
        return true;
    }

    public function hasLayout(): bool
    {
        return false;
    }

    public function getLayoutName(RenderingContextInterface $renderingContext): ?string
    {
        return '';
    }

    public function addCompiledNamespaces(RenderingContextInterface $renderingContext): void {}
}
