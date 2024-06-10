<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Schema;

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

/**
 * @internal
 */
final class ViewHelperMetadata
{
    /**
     * @param array<string, string> $docTags
     * @param array<string, ArgumentDefinition> $argumentDefinitions
     */
    public function __construct(
        public readonly string $className,
        public readonly string $namespace,
        public readonly string $name,
        public readonly string $tagName,
        public readonly string $documentation,
        public readonly string $xmlNamespace,
        public readonly array $docTags,
        public readonly array $argumentDefinitions,
        public readonly bool $allowsArbitraryArguments,
    ) {}
}
