<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\ViewHelper\UnresolvableViewHelperException;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

abstract class AbstractComponentCollection implements ViewHelperResolverDelegateInterface, ComponentResolverInterface
{
    public function resolveTemplateName(string $viewHelperName): string
    {
        $componentNameFragments = explode('.', $viewHelperName);
        return implode(DIRECTORY_SEPARATOR, array_map(ucfirst(...), $componentNameFragments));
    }

    public function resolveViewHelperClassName(string $viewHelperName): string
    {
        $expectedTemplateName = $this->resolveTemplateName($viewHelperName);
        if (!$this->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $expectedTemplateName)) {
            throw new UnresolvableViewHelperException(sprintf(
                'Based on your spelling, the system would load the component template "%s", however this file does not exist.',
                $expectedTemplateName,
            ), 1748511297);
        };
        return ComponentRenderer::class;
    }

    public function getAdditionalVariables(): array
    {
        return [];
    }

    public function getNamespace(): string
    {
        return static::class;
    }
}
