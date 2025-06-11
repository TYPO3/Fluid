<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * @api
 * @see ComponentDefinitionProviderInterface
 */
interface ComponentRendererInterface
{
    /**
     * @param array<string, mixed> $arguments
     * @param array<string, \Closure> $slots
     */
    public function renderComponent(string $viewHelperName, array $arguments, array $slots, RenderingContextInterface $parentRenderingContext): string;
}
