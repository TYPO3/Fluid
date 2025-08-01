<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

final class ParsedTemplateImplementationFixture implements ParsedTemplateInterface
{
    public function setIdentifier(string $identifier): void
    {
        // stub
    }

    public function getIdentifier(): string
    {
        return 'myIdentifier';
    }

    public function getArgumentDefinitions(): array
    {
        return [];
    }

    public function getAvailableSlots(): array
    {
        return [];
    }

    public function render(RenderingContextInterface $renderingContext): mixed
    {
        return 'rendered by fixture';
    }

    public function getVariableContainer(): VariableProviderInterface
    {
        return new StandardVariableProvider();
    }

    public function getLayoutName(RenderingContextInterface $renderingContext): ?string
    {
        return null;
    }

    public function addCompiledNamespaces(RenderingContextInterface $renderingContext): void
    {
        // stub
    }

    public function hasLayout(): bool
    {
        return false;
    }

    public function isCompilable(): bool
    {
        return false;
    }

    public function isCompiled(): bool
    {
        return false;
    }
}
