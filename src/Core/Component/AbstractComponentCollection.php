<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

abstract class AbstractComponentCollection implements ViewHelperResolverDelegateInterface, ComponentViewFactoryInterface
{
    public function prepareTemplateName(string $viewHelperName): string
    {
        $componentNameFragments = explode('.', $viewHelperName);
        return implode(DIRECTORY_SEPARATOR, array_map(ucfirst(...), $componentNameFragments));
    }

    public function resolveViewHelperClassName(string $name): string
    {
        return ComponentRenderer::class;
    }

    public function getNamespace(): string
    {
        return static::class;
    }
}
