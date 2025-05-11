<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * @internal This interface should only be used for type hinting
 */
interface ViewHelperResolverDelegateInterface
{
    /**
     * @throws UnresolvableViewHelperException
     */
    public function resolveViewHelperClassName(string $name): string;

    public function __toString(): string;
}
