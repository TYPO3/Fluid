<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * @internal
 */
interface ArgumentProcessorInterface
{
    public function process(mixed $value, ArgumentDefinition $definition): mixed;
    public function isValid(mixed $value, ArgumentDefinition $definition): bool;
}
