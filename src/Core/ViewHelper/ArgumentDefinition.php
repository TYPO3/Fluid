<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Definition\Annotation\ArgumentAnnotationInterface;

/**
 * Argument definition of each view helper argument
 *
 * @todo define readonly with Fluid 6
 */
class ArgumentDefinition
{
    public function __construct(
        protected string $name,
        protected string $type,
        protected string $description,
        protected bool $required,
        protected mixed $defaultValue = null,
        /**
        * @var bool|null Escaping instruction, in line with $this->escapeOutput / $this->escapeChildren on ViewHelpers.
        *                "null" means "use default behavior" (which is to escape nodes contained in the value).
        *                "true" means "escape unless escaping is disabled" (e.g. if argument is used in a ViewHelper nested
        *                within f:format.raw which disables escaping, the argument will not be escaped).
        *                "false" means "never escape argument" (as in behavior of f:format.raw, which supports both passing
        *                argument as actual argument or as tag content, but wants neither to be escaped).
        */
        protected ?bool $escape = null,
        /** @var ArgumentAnnotationInterface[] */
        protected array $annotations = [],
    ) {
        if ($required && $defaultValue !== null) {
            throw new \InvalidArgumentException(
                sprintf('ArgumentDefinition "%s" cannot have a default value while also being required. Either remove the default or mark it as optional.', $name),
                1754235900,
            );
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getEscape(): ?bool
    {
        return $this->escape;
    }

    /**
     * @return ArgumentAnnotationInterface[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    public function isBooleanType(): bool
    {
        return $this->getType() === 'bool' || $this->getType() === 'boolean';
    }

    /**
     * @return string[]
     */
    public function getUnionTypes(): array
    {
        return array_map('trim', explode('|', $this->getType()));
    }

    /**
     * @internal Only to be used by TemplateCompiler
     */
    public function compile(): string
    {
        return sprintf(
            'new ' . static::class . '(%s, %s, %s, %s, %s, %s, [%s])',
            var_export($this->getName(), true),
            var_export($this->getType(), true),
            var_export($this->getDescription(), true),
            var_export($this->isRequired(), true),
            var_export($this->getDefaultValue(), true),
            var_export($this->getEscape(), true),
            implode(',', array_map(
                static fn(ArgumentAnnotationInterface $annotation): string => $annotation->compile(),
                $this->getAnnotations(),
            )),
        );
    }
}
