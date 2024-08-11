<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables\Fixtures;

/**
 * Used by StandardVariableProviderTest
 */
final class StandardVariableProviderModelFixture
{
    public string $existingPublicProperty = 'existingPublicPropertyValue';

    public function __construct(private readonly string $name) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function isNamed(): bool
    {
        return !empty($this->name);
    }

    public function hasSomeName(): bool
    {
        return !empty($this->name);
    }
}
