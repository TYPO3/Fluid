<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections;

use TYPO3Fluid\Fluid\Core\Component\ComponentAdapter;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

/**
 * ViewHelperResolver delegate that tries to use the ComponentAdapter, but doesn't
 * implement the required interface
 */
final readonly class InvalidComponentCollection implements ViewHelperResolverDelegateInterface
{
    public function resolveViewHelperClassName(string $viewHelperName): string
    {
        return ComponentAdapter::class;
    }

    public function getNamespace(): string
    {
        return self::class;
    }
}
