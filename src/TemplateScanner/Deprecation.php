<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\TemplateScanner;

/**
 * @internal
 */
final readonly class Deprecation
{
    public function __construct(
        public string $file,
        public int $line,
        public string $message,
    ) {}
}
