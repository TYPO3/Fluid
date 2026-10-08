<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Rendering;

/**
 * Converts enum cases to text while leaving other values unchanged.
 *
 * @internal
 */
final class EnumConverter
{
    public static function convert(mixed $value): mixed
    {
        return match (true) {
            $value instanceof \BackedEnum => (string)$value->value,
            $value instanceof \UnitEnum => $value->name,
            default => $value,
        };
    }
}
