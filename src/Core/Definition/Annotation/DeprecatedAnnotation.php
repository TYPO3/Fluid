<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Definition\Annotation;

/**
 * @internal
 */
final readonly class DeprecatedAnnotation implements ViewHelperAnnotationInterface, ArgumentAnnotationInterface, CompilableAnnotationInterface
{
    public function __construct(public string $description) {}

    public function compile(): string
    {
        return 'new ' . static::class . '(' . var_export($this->description, true) . ')';
    }
}
