<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Variables;

/**
 * Allows chaining any number of prioritised VariableProviders
 * to be consulted whenever a variable is requested. First
 * VariableProvider to return a value "wins".
 */
class ChainedVariableProvider extends StandardVariableProvider implements VariableProviderInterface
{
    /**
     * @var VariableProviderInterface[]
     */
    protected array $variableProviders = [];

    /**
     * @param VariableProviderInterface[] $variableProviders
     */
    public function __construct(array $variableProviders = [])
    {
        $this->variableProviders = $variableProviders;
    }

    /**
     * @return VariableProviderInterface[]
     */
    public function getAll(): array
    {
        $merged = [];
        foreach (array_reverse($this->variableProviders) as $provider) {
            $merged = array_replace_recursive($merged, $provider->getAll());
        }
        return array_merge($merged, $this->variables);
    }

    public function get(string $identifier): mixed
    {
        if (array_key_exists($identifier, $this->variables)) {
            return $this->variables[$identifier];
        }
        foreach ($this->variableProviders as $provider) {
            $value = $provider->get($identifier);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

    public function getByPath(string $path): mixed
    {
        if (array_key_exists($path, $this->variables)) {
            return $this->variables[$path];
        }
        // We did not resolve with native StandardVariableProvider. Let's try the chain.
        foreach ($this->variableProviders as $provider) {
            $value = $provider->getByPath($path);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

    public function getAllIdentifiers(): array
    {
        $merged = parent::getAllIdentifiers();
        foreach ($this->variableProviders as $provider) {
            $merged = array_replace_recursive($merged, $provider->getAllIdentifiers());
        }
        return array_values(array_unique($merged));
    }

    public function getScopeCopy(array|\ArrayAccess $variables): ChainedVariableProvider
    {
        $clone = clone $this;
        $clone->setSource($variables);
        return $clone;
    }
}
