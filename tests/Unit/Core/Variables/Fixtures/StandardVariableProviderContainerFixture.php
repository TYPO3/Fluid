<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables\Fixtures;

use Psr\Container\ContainerInterface;

/**
 * Used by StandardVariableProviderTest
 */
final readonly class StandardVariableProviderContainerFixture implements ContainerInterface
{
    public function __construct(private array $properties) {}

    public function get(string $id): mixed
    {
        return $this->properties[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->properties);
    }
}
