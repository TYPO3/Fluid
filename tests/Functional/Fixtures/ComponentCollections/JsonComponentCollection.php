<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections;

use TYPO3Fluid\Fluid\Core\Component\ComponentAdapter;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentRendererInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

/**
 * This is just an example to test a different component renderer
 */
final readonly class JsonComponentCollection implements ViewHelperResolverDelegateInterface, ComponentDefinitionProviderInterface
{
    public function getComponentDefinition(string $viewHelperName): ComponentDefinition
    {
        return new ComponentDefinition($viewHelperName, [], true, []);
    }

    public function getComponentRenderer(): ComponentRendererInterface
    {
        return new JsonComponentRenderer();
    }

    public function resolveViewHelperClassName(string $viewHelperName): string
    {
        return ComponentAdapter::class;
    }

    public function getNamespace(): string
    {
        return static::class;
    }
}
