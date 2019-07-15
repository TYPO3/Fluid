<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Interface for an argument definition
 *
 * An argument definition is a named, valued and type-specific
 * component; see those interfaces for details.
 */
interface ArgumentDefinitionInterface
{
    /**
     * Constructor for this argument definition.
     *
     * @param string $name Name of argument
     * @param string $type Type of argument
     * @param string $description Description of argument
     * @param boolean $required TRUE if argument is required
     * @param mixed $defaultValue Default value
     */
    public function __construct(string $name, string $type, string $description, bool $required, $defaultValue = null);

    /**
     * Get the name of the argument
     *
     * @return string Name of argument
     */
    public function getName(): string;

    /**
     * Get the description of the argument
     *
     * @return string Description of argument
     */
    public function getDescription(): string;

    /**
     * Get the default value, if set
     *
     * @return mixed Default value
     */
    public function getDefaultValue();
}
