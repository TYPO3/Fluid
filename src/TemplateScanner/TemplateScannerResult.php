<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\TemplateScanner;

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;

/**
 * @internal
 */
final readonly class TemplateScannerResult
{
    /**
     * @param \Exception[] $errors
     * @param Deprecation[] $deprecations
     */
    public function __construct(
        public string $identifier,
        public string $path,
        public array $errors,
        public array $deprecations,
        public ?ParsingState $parsedTemplate,
    ) {}

    public function canBeCompiled(): bool
    {
        return $this->errors === [] && $this->parsedTemplate?->isCompilable();
    }
}
