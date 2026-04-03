<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Validation;

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\TemplateLocationException;

/**
 * @internal
 */
final readonly class TemplateValidatorResult implements \JsonSerializable
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

    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier,
            'path' => $this->path,
            'errors' => array_map(fn($e) => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                ...($e instanceof TemplateLocationException ? ['templateLocation' => get_object_vars($e->getTemplateLocation())] : []),
            ], $this->errors),
            'deprecations' => array_map(fn($deprecation) => get_object_vars($deprecation), $this->deprecations),
        ];
    }
}
