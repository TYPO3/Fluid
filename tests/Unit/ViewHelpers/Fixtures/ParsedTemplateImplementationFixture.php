<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * Fixture for a parsed template
 */
class ParsedTemplateImplementationFixture implements ParsedTemplateInterface
{
    public function flatten(bool $extractNode = false)
    {
        // TODO: Implement flatten() method.
    }

    public function onOpen(RenderingContextInterface $renderingContext, ?ArgumentCollection $arguments = null): ComponentInterface
    {
        return $this;
    }

    public function onClose(RenderingContextInterface $renderingContext): ComponentInterface
    {
        return $this;
    }

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

    public function isCompilable(): bool
    {
        // stub
    }

    public function isCompiled(): bool
    {
        // stub
    }

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollection $arguments = null)
    {
        // TODO: Implement execute() method.
    }

    public function addArgumentDefinition(ArgumentDefinition $definition): ComponentInterface
    {
        return $this;
    }

    public function createArgumentDefinitions(): ArgumentCollection
    {
        // TODO: Implement createArgumentDefinitions() method.
    }

    public function getName(): ?string
    {
        // TODO: Implement getName() method.
    }

    public function getArguments(): ArgumentCollection
    {
        // TODO: Implement getArguments() method.
    }

    public function addChild(ComponentInterface $component): ComponentInterface
    {
        return $this;
    }

    public function getNamedChild(string $name): ComponentInterface
    {
        return $this;
    }

    public function getChildren(): iterable
    {
        // TODO: Implement getChildren() method.
    }

    public function allowUndeclaredArgument(string $argumentName): bool
    {
        return true;
    }

    public function isChildrenEscapingEnabled(): bool
    {
        return true;
    }

    public function isOutputEscapingEnabled(): bool
    {
        return false;
    }

}
