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
 * @api
 */
interface ViewHelperDefinitionInterface
{
    /**
     * Returns the name of the ViewHelper, as it would be used in the template
     * (e. g. "format.date")
     */
    public function getName(): string;

    /**
     * @return array<string, ArgumentDefinition>
     */
    public function getArgumentDefinitions(): array;

    public function additionalArgumentsAllowed(): bool;

    public function getDocumentation(): string;

    /**
     * @return ViewHelperAnnotationInterface[]
     */
    public function getAnnotations(): array;
}
