<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

/**
 * @api
 */
final readonly class ComponentDefinition
{
    /**
     * @param array<string, ArgumentDefinition> $argumentDefinitions
     * @param string[] $availableSlots
     */
    public function __construct(
        private string $name,
        private array $argumentDefinitions,
        private bool $additionalArgumentsAllowed,
        private array $availableSlots,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, ArgumentDefinition>
     */
    public function getArgumentDefinitions(): array
    {
        return $this->argumentDefinitions;
    }

    public function additionalArgumentsAllowed(): bool
    {
        return $this->additionalArgumentsAllowed;
    }

    /**
     * @return string[]
     */
    public function getAvailableSlots(): array
    {
        return $this->availableSlots;
    }
}
