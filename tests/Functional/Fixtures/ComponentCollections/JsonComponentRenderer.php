<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections;

use TYPO3Fluid\Fluid\Core\Component\ComponentRendererInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This is just an example to test a different component renderer
 */
final readonly class JsonComponentRenderer implements ComponentRendererInterface
{
    public function renderComponent(string $viewHelperName, array $arguments, array $slots, RenderingContextInterface $parentRenderingContext): string
    {
        return json_encode(['component' => $viewHelperName, 'arguments' => $arguments]);
    }
}
