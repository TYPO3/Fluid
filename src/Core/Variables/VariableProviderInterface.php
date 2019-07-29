<?php
declare(strict_types=1);
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
interface VariableProviderInterface
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
     * @param array $variables
     * @return VariableProviderInterface
     */
    public function getScopeCopy(array $variables): VariableProviderInterface;

    /**
     * Set the source data used by this VariableProvider. The
     * source can be any type, but the type must of course be
     * supported by the VariableProvider itself.
     *
     * @param mixed $source
     * @return void
     */
    public function setSource($source): void;

    public function getSource();

    public function getAll(): array;

    public function add(string $identifier, $value): void;

    /**
     * Get a variable from the context.
     *
     * @param string $identifier
     * @return mixed The variable value identified by $identifier
     */
    public function get(string $identifier);

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
    public function getByPath(string $path, array $accessors = []);

    public function remove(string $identifier): void;

    public function getAllIdentifiers(): array;

    public function exists(string $identifier): bool;
}
