<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * Fixture for a parsed template
 */
class ParsedTemplateImplementationFixture implements ParsedTemplateInterface
{

    public function setIdentifier($identifier): void
    {
        // stub
    }

    public function getIdentifier(): string
    {
        // stub
    }

    public function render(RenderingContextInterface $renderingContext)
    {
        return 'rendered by fixture';
    }

    public function getVariableContainer(): VariableProviderInterface
    {
        // stub
    }

    public function getLayoutName(RenderingContextInterface $renderingContext): string
    {
        // stub
    }

    public function addCompiledNamespaces(RenderingContextInterface $renderingContext): void
    {
        // stub
    }

    public function hasLayout(): bool
    {
        // stub
    }

    public function isCompilable(): bool
    {
        // stub
    }

    public function isCompiled(): bool
    {
        // stub
    }
}
