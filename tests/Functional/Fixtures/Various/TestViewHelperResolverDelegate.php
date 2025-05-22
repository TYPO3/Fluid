<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

use TYPO3Fluid\Fluid\Core\ViewHelper\UnresolvableViewHelperException;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

final class TestViewHelperResolverDelegate implements ViewHelperResolverDelegateInterface
{
    public function resolveViewHelperClassName(string $name): string
    {
        // ViewHelpers in different location, without "ViewHelper" suffix and with _ as directory separator
        $name = implode('_', array_map(ucfirst(...), explode('.', $name)));
        $className = 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers\\TestViewHelperResolverDelegate\\' . $name;
        if (!class_exists($className)) {
            throw new UnresolvableViewHelperException('Class ' . $className . ' does not exist.', 1747931176);
        }
        return $className;
    }

    public function getNamespace(): string
    {
        return self::class;
    }
}
