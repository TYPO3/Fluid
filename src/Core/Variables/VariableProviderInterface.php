<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Variables;

/**
 * Interface VariableProviderInterface
 *
 * Implemented by classes able to provide variables
 * for a Fluid template rendering.
 *
 * Your VariableProvider implementation does not
 * have to allow setting variables or use the
 * constructor variables argument for anything, but
 * should at least implement the getting methods.
 */
interface VariableProviderInterface extends \ArrayAccess
{
    /**
     * Gets a fresh instance of this type of VariableProvider
     * and fills it with the variables passed in $variables.
     *
     * Can be overridden to enable special instance creation
     * of the new VariableProvider as well as take care of any
     * automatically transferred variables (in the default
     * implementation the $settings variable is transferred).
     */
    public function getScopeCopy(array|\ArrayAccess $variables): VariableProviderInterface;

    /**
     * Set the source data used by this VariableProvider. The
     * source can be any type, but the type must of course be
     * supported by the VariableProvider itself.
     */
    public function setSource(mixed $source): void;

    public function getSource(): mixed;

    /**
     * Get every variable provisioned by the VariableProvider
     * implementing the interface. Must return an array or
     * ArrayAccess instance!
     */
    public function getAll(): array|\ArrayAccess;

    /**
     * Add a variable to the context
     *
     * @param string $identifier Identifier of the variable to add
     * @param mixed $value The variable's value
     * @api
     */
    public function add(string $identifier, mixed $value): void;

    /**
     * Get a variable from the context.
     *
     * @return mixed The variable value identified by $identifier
     * @api
     */
    public function get(string $identifier): mixed;

    /**
     * Get a variable by dotted path expression, retrieving the
     * variable from nested arrays/objects one segment at a time.
     */
    public function getByPath(string $path): mixed;

    /**
     * Remove a variable from context.
     *
     * @param string $identifier The identifier to remove
     * @api
     */
    public function remove(string $identifier): void;

    /**
     * Returns an array of all identifiers available in the context.
     *
     * @return string[] Array of identifier strings
     */
    public function getAllIdentifiers(): array;

    /**
     * Checks if this property exists in the VariableContainer.
     *
     * @return bool true if $identifier exists
     * @api
     */
    public function exists(string $identifier): bool;

    /**
     * Adds a variable to the context.
     */
    public function offsetSet(mixed $identifier, mixed $value): void;

    /**
     * Remove a variable from context.
     */
    public function offsetUnset(mixed $identifier): void;

    /**
     * Checks if this property exists in the VariableContainer.
     */
    public function offsetExists(mixed $identifier): bool;

    /**
     * Get a variable from the context.
     */
    public function offsetGet(mixed $identifier): mixed;
}
