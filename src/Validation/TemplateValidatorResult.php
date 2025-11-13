<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Validation;

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;

/**
 * @internal
 */
final readonly class TemplateValidatorResult
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

    /**
     * Creates a copy with different errors. This allows
     * to attach errors after the object has been created,
     * e. g. errors happening during template compilation
     *
     * @param \Exception[] $errors
     */
    public function withErrors(array $errors): self
    {
        return new self(
            identifier: $this->identifier,
            path: $this->path,
            errors: $errors,
            deprecations: $this->deprecations,
            parsedTemplate: $this->parsedTemplate,
        );
    }

    public function canBeCompiled(): bool
    {
        return $this->errors === [] && $this->parsedTemplate?->isCompilable();
    }
}
