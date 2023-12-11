<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Variables;

/**
 * Variable provider to be used in cases where a specific
 * set of variables are only valid in a local context, while
 * another set of global variables should remain valid after
 * that context. This is used for example for AliasViewHelper
 * or ForViewHelper to differentiate the variables provided
 * for child elements from global variables that should still
 * be valid afterwards.
 */
final class ScopedVariableProvider extends StandardVariableProvider implements VariableProviderInterface
{
    public function __construct(
        protected VariableProviderInterface $globalVariables,
        protected VariableProviderInterface $localVariables,
    ) {}

    public function getGlobalVariableProvider(): VariableProviderInterface
    {
        return $this->globalVariables;
    }

    public function getLocalVariableProvider(): VariableProviderInterface
    {
        return $this->localVariables;
    }

    /**
     * @param string $identifier Identifier of the variable to add
     * @param mixed $value The variable's value
     */
    public function add($identifier, $value): void
    {
        $this->globalVariables->add($identifier, $value);
        $this->localVariables->add($identifier, $value);
    }

    /**
     * @param string $identifier The identifier to remove
     */
    public function remove($identifier): void
    {
        $this->globalVariables->remove($identifier);
        $this->localVariables->remove($identifier);
    }

    /**
     * @param mixed $source
     */
    public function setSource($source): void
    {
        $this->globalVariables->setSource($source);
    }

    public function getSource(): array
    {
        return $this->getAll();
    }

    public function getAll(): array
    {
        return array_merge(
            $this->globalVariables->getAll(),
            $this->localVariables->getAll(),
        );
    }

    /**
     * @param string $identifier
     */
    public function exists($identifier): bool
    {
        return $this->localVariables->exists($identifier) || $this->globalVariables->exists($identifier);
    }

    /**
     * @param string $identifier
     */
    public function get($identifier): mixed
    {
        return $this->localVariables->get($identifier) ?? $this->globalVariables->get($identifier);
    }

    /**
     * @param string $path
     */
    public function getByPath($path): mixed
    {
        $path = $this->resolveSubVariableReferences($path);
        $identifier = explode('.', $path, 2)[0];
        return $this->localVariables->exists($identifier)
            ? $this->localVariables->getByPath($path)
            : $this->globalVariables->getByPath($path);
    }

    public function getAllIdentifiers(): array
    {
        return array_unique(array_merge(
            $this->globalVariables->getAllIdentifiers(),
            $this->localVariables->getAllIdentifiers(),
        ));
    }

    /**
     * @param array|\ArrayAccess $variables
     */
    public function getScopeCopy($variables): VariableProviderInterface
    {
        // Instead of cloning the instance of ScopedVariableProvider,
        // only the instance holding global variables can be used here.
        // Local variables are irrelevant for partials and sections
        // because all variables are provided explicity via $variables.
        // "settings" should leak down into partials and sections, but
        // this is already implemented in StandardVariableProvider
        return $this->globalVariables->getScopeCopy($variables);
    }
}
