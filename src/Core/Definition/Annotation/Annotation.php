<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Definition\Annotation;

/**
 * Generic annotation implementation that works both for ViewHelpers/Components
 * and their arguments. This can be used to add arbitrary information, such as
 * a link to relevant documentation or flags like "internal" to ViewHelpers/Components
 * and their arguments.
 *
 * For more advanced use cases, or when type safety is preferred, the relevant
 * interfaces can be implemented by a custom annotation class (e. g. DeprecationAnnotation,
 * DocumentationAnnotation, ...).
 *
 * @internal
 */
final readonly class Annotation implements ViewHelperAnnotationInterface, ArgumentAnnotationInterface
{
    public function __construct(public array $data) {}

    public function compile(): string
    {
        return 'new ' . static::class . '(' . var_export($this->data, true) . ')';
    }
}
