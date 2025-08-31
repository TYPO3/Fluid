<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * Argument definition of each view helper argument
 */
class ArgumentDefinition
{
    /**
     * Name of argument
     */
    protected string $name;

    /**
     * Type of argument
     */
    protected string $type;

    /**
     * Description of argument
     */
    protected string $description;

    /**
     * Is argument required?
     */
    protected bool $required = false;

    /**
     * Default value for argument
     */
    protected mixed $defaultValue;

    /**
     * Escaping instruction, in line with $this->escapeOutput / $this->escapeChildren on ViewHelpers.
     *
     * "null" means "use default behavior" (which is to escape nodes contained in the value).
     *
     * "true" means "escape unless escaping is disabled" (e.g. if argument is used in a ViewHelper nested
     * within f:format.raw which disables escaping, the argument will not be escaped).
     *
     * "false" means "never escape argument" (as in behavior of f:format.raw, which supports both passing
     * argument as actual argument or as tag content, but wants neither to be escaped).
     */
    protected ?bool $escape;

    /**
     * Constructor for this argument definition.
     *
     * @param string $name Name of argument
     * @param string $type Type of argument
     * @param string $description Description of argument
     * @param bool $required true if argument is required
     * @param mixed $defaultValue Default value
     * @param bool|null $escape Whether argument is escaped, or uses default escaping behavior (see class var comment)
     */
    public function __construct(string $name, string $type, string $description, bool $required, mixed $defaultValue = null, ?bool $escape = null)
    {
        if ($required && $defaultValue !== null) {
            throw new \InvalidArgumentException(
                sprintf('ArgumentDefinition "%s" cannot have a default value while also being required. Either remove the default or mark it as optional.', $name),
                1754235900,
            );
        }

        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
        $this->escape = $escape;
    }

    /**
     * Get the name of the argument
     *
     * @return string Name of argument
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the type of the argument
     *
     * @return string Type of argument
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the description of the argument
     *
     * @return string Description of argument
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the optionality of the argument
     *
     * @return bool true if argument is optional
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get the default value, if set
     *
     * @return mixed Default value
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @return bool|null
     */
    public function getEscape(): ?bool
    {
        return $this->escape;
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
        return array_map('trim', explode('|', $this->type));
    }
}
