<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Definition;

use TYPO3Fluid\Fluid\Core\Definition\Annotation\ViewHelperAnnotationInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

/**
 * Definition of a ViewHelper in the context of a namespace
 *
 * @api
 */
final readonly class ViewHelperDefinition implements ViewHelperDefinitionInterface
{
    /**
     * @param array<string, ArgumentDefinition> $argumentDefinitions
     * @param ViewHelperAnnotationInterface[] $annotations
     */
    public function __construct(
        private string $name,
        private array $argumentDefinitions,
        private bool $additionalArgumentsAllowed,
        private string $documentation = '',
        private array $annotations = [],
    ) {
    }

    /**
     * Returns the name of the ViewHelper, as it would be used in the template
     * (e. g. "format.date")
     */
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

    public function getDocumentation(): string
    {
        return $this->documentation;
    }

    /**
     * @return ViewHelperAnnotationInterface[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }
}
