<?php
namespace TYPO3Fluid\Fluid\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
     * Variables, if any, with which to initialize this
     * VariableProvider.
     *
     * @param array $variables
     */
    public function __construct(array $variables = []);

    /**
     * Gets a fresh instance of this type of VariableProvider
     * and fills it with the variables passed in $variables.
     *
     * Can be overridden to enable special instance creation
     * of the new VariableProvider as well as take care of any
     * automatically transferred variables (in the default
     * implementation the $settings variable is transferred).
     *
     * @param array|\ArrayAccess $variables
     * @return VariableProviderInterface
     */
    public function getScopeCopy($variables);

    /**
     * Set the source data used by this VariableProvider. The
     * source can be any type, but the type must of course be
     * supported by the VariableProvider itself.
     *
     * @param mixed $source
     * @return void
     */
    public function setSource($source);

    /**
     * @return mixed
     */
    public function getSource();

    /**
     * Get every variable provisioned by the VariableProvider
     * implementing the interface. Must return an array or
     * ArrayAccess instance!
     *
     * @return array|\ArrayAccess
     */
    public function getAll();

    /**
     * Add a variable to the context
     *
     * @param string $identifier Identifier of the variable to add
     * @param mixed $value The variable's value
     * @return void
     * @api
     */
    public function add($identifier, $value);

    /**
     * Get a variable from the context.
     *
     * @param string $identifier
     * @return mixed The variable value identified by $identifier
     * @api
     */
    public function get($identifier);

    /**
     * Get a variable by dotted path expression, retrieving the
     * variable from nested arrays/objects one segment at a time.
     * If the second variable is passed, it is expected to contain
     * extraction method names (constants from VariableExtractor)
     * which indicate how each value is extracted.
     *
     * @param string $path
     * @param array $accessors
     * @return mixed
     */
    public function getByPath($path, array $accessors = []);

    /**
     * Remove a variable from context.
     *
     * @param string $identifier The identifier to remove
     * @return void
     * @api
     */
    public function remove($identifier);

    /**
     * Returns an array of all identifiers available in the context.
     *
     * @return array Array of identifier strings
     */
    public function getAllIdentifiers();

    /**
     * Checks if this property exists in the VariableContainer.
     *
     * @param string $identifier
     * @return boolean TRUE if $identifier exists, FALSE otherwise
     * @api
     */
    public function exists($identifier);

    /**
     * Adds a variable to the context.
     *
     * @param string $identifier Identifier of the variable to add
     * @param mixed $value The variable's value
     * @return void
     */
    public function offsetSet($identifier, $value);

    /**
     * Remove a variable from context.
     *
     * @param string $identifier The identifier to remove
     * @return void
     */
    public function offsetUnset($identifier);

    /**
     * Checks if this property exists in the VariableContainer.
     *
     * @param string $identifier
     * @return boolean TRUE if $identifier exists, FALSE otherwise
     */
    public function offsetExists($identifier);

    /**
     * Get a variable from the context.
     *
     * @param string $identifier
     * @return mixed The variable identified by $identifier
     */
    public function offsetGet($identifier);
}
