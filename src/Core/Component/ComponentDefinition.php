<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Definition\Annotation\ViewHelperAnnotationInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

/**
 * @api
 */
final readonly class ComponentDefinition
{
    public function __construct(
        private string $name,
        /** @var array<string, ArgumentDefinition> */
        private array $argumentDefinitions,
        private bool $additionalArgumentsAllowed,
        /** @var string[] */
        private array $availableSlots,
        /** @var ViewHelperAnnotationInterface[] */
        private array $annotations = [],
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

    /**
     * @return ViewHelperAnnotationInterface[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }
}
