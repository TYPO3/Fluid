<?php

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
    protected $variableProviders = [];

    /**
     * @param array $variableProviders
     */
    public function __construct(array $variableProviders = [])
    {
        $this->variableProviders = $variableProviders;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $merged = [];
        foreach (array_reverse($this->variableProviders) as $provider) {
            $merged = array_replace_recursive($merged, $provider->getAll());
        }
        return array_merge($merged, $this->variables);
    }

    /**
     * @param string $identifier
     * @return mixed
     */
    public function get($identifier)
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

    /**
     * @param string $path
     * @param array $accessors
     * @return mixed|null
     */
    public function getByPath($path, array $accessors = [])
    {
        // First, see if we can resolve the value directly using "native" StandardVariableProvider.
        // Even though this class extends StandardVariableProvider, we need to instantiate a new
        // instance of StandardVariableProvider since getByPath() of parent calls itself recursive,
        // and we must not become a victim of late-static binding here.
        // @todo: It *might* be possible to simplify this, to:
        //        $standardVariableProvider = new StandardVariableProvider($this->variables);
        //        standardVariableProvider->getByPath($path, $accessors);
        //        With current test coverage, this works. However, the old VariableExtractor
        //        accepted mixed as variables, while StandardVariableProvider only accepts array.
        //        We're currently unsure if this is a problem, it should be investigated further
        //        to see if the given solution should be kept and this comment should be removed,
        //        or if the code could be simplified.
        // @todo: Also, this is unclear: The chained variable provider should most likely call
        //        *only* variable resolving for attached single providers, and not use the
        //        StandardVariableProvider as standard solution. As such, this default should
        //        probably vanish altogether, to then rely on single variable provider implementations
        //        instead, without this magic resolver?
        // @todo: All in all, the entire VariableProvider construct feels over abstracted covering
        //        very seldom use cases only. We may want to look at this again to see if we could
        //        simplify the entire construct again.
        $standardVariableProvider = new StandardVariableProvider();
        $standardVariableProvider->setSource($this->variables);
        $value = $standardVariableProvider->getByPath($path, $accessors);
        if ($value !== null) {
            return $value;
        }
        // We did not resolve with native StandardVariableProvider. Let's try the chain.
        foreach ($this->variableProviders as $provider) {
            $value = $provider->getByPath($path, $accessors);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getAllIdentifiers()
    {
        $merged = parent::getAllIdentifiers();
        foreach ($this->variableProviders as $provider) {
            $merged = array_replace_recursive($merged, $provider->getAllIdentifiers());
        }
        return array_values(array_unique($merged));
    }

    /**
     * @param array|\ArrayAccess $variables
     * @return ChainedVariableProvider
     */
    public function getScopeCopy($variables)
    {
        $clone = clone $this;
        $clone->setSource($variables);
        return $clone;
    }
}
