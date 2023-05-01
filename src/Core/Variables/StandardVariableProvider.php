<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Variables;

/**
 * Class StandardVariableProvider
 */
class StandardVariableProvider implements VariableProviderInterface
{
    /**
     * Variables stored in context
     *
     * @var mixed
     */
    protected $variables = [];

    /**
     * Runtime cache to speed up object access through fluid
     *
     * @var array
     */
    protected $objectAccessorCache = [];

    /**
     * Variables, if any, with which to initialize this
     * VariableProvider.
     *
     * @param array $variables
     */
    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    /**
     * @param array|\ArrayAccess $variables
     * @return VariableProviderInterface
     */
    public function getScopeCopy($variables)
    {
        if (!array_key_exists('settings', $variables) && array_key_exists('settings', $this->variables)) {
            $variables['settings'] = $this->variables['settings'];
        }
        $className = get_class($this);
        $variableProvider = new $className($variables);
        $variableProvider->setObjectAccessorCacheData($this->getObjectAccessorCacheData());
        return $variableProvider;
    }

    /**
     * Pre-fills the object accessor cache to speed up access to
     * object properties with fluid
     *
     * @param array $cache
     */
    public function setObjectAccessorCacheData(array $cache): void
    {
        $this->objectAccessorCache = $cache;
    }

    /**
     * Returns the object accessor cache data to be used in a different
     * variable provider instance
     *
     * @return array
     */
    public function getObjectAccessorCacheData(): array
    {
        return $this->objectAccessorCache;
    }

    /**
     * Set the source data used by this VariableProvider. The
     * source can be any type, but the type must of course be
     * supported by the VariableProvider itself.
     *
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->variables = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->variables;
    }

    /**
     * Get every variable provisioned by the VariableProvider
     * implementing the interface. Must return an array or
     * ArrayAccess instance!
     *
     * @return array|\ArrayAccess
     */
    public function getAll()
    {
        return $this->variables;
    }

    /**
     * Add a variable to the context
     *
     * @param string $identifier Identifier of the variable to add
     * @param mixed $value The variable's value
     * @api
     */
    public function add($identifier, $value)
    {
        $this->variables[$identifier] = $value;
    }

    /**
     * Get a variable from the context. Throws exception if variable is not found in context.
     *
     * If "_all" is given as identifier, all variables are returned in an array,
     * if one of the other reserved variables are given, their appropriate value
     * they're representing is returned.
     *
     * @param string $identifier
     * @return mixed The variable value identified by $identifier
     * @api
     */
    public function get($identifier)
    {
        return $this->getByPath($identifier);
    }

    /**
     * Get a variable by dotted path expression, retrieving the
     * variable from nested arrays/objects one segment at a time.
     * If the second variable is passed, it is expected to contain
     * extraction method names (constants from this class)
     * which indicate how each value is extracted.
     *
     * @param string $path
     * @return mixed
     */
    public function getByPath($path)
    {
        $subject = $this->variables;
        $subVariableReferences = explode('.', $this->resolveSubVariableReferences($path));
        foreach ($subVariableReferences as $pathSegment) {
            $subject = $this->extract($subject, $pathSegment);
            if ($subject === null) {
                break;
            }
        }
        return $subject;
    }

    /**
     * Remove a variable from context. Throws exception if variable is not found in context.
     *
     * @param string $identifier The identifier to remove
     * @api
     */
    public function remove($identifier)
    {
        if (array_key_exists($identifier, $this->variables)) {
            unset($this->variables[$identifier]);
        }
    }

    /**
     * Returns an array of all identifiers available in the context.
     *
     * @return array Array of identifier strings
     */
    public function getAllIdentifiers()
    {
        return array_keys($this->variables);
    }

    /**
     * Checks if this property exists in the VariableContainer.
     *
     * @param string $identifier
     * @return bool TRUE if $identifier exists, FALSE otherwise
     * @api
     */
    public function exists($identifier)
    {
        return array_key_exists($identifier, $this->variables);
    }

    /**
     * Clean up for serializing.
     *
     * @return string[]
     */
    public function __sleep()
    {
        return ['variables'];
    }

    /**
     * Adds a variable to the context.
     *
     * @param string $identifier Identifier of the variable to add
     * @param mixed $value The variable's value
     */
    public function offsetSet($identifier, $value)
    {
        $this->add($identifier, $value);
    }

    /**
     * Remove a variable from context. Throws exception if variable is not found in context.
     *
     * @param string $identifier The identifier to remove
     */
    public function offsetUnset($identifier)
    {
        $this->remove($identifier);
    }

    /**
     * Checks if this property exists in the VariableContainer.
     *
     * @param string $identifier
     * @return bool TRUE if $identifier exists, FALSE otherwise
     */
    public function offsetExists($identifier)
    {
        return $this->exists($identifier);
    }

    /**
     * Get a variable from the context. Throws exception if variable is not found in context.
     *
     * @param string $identifier
     * @return mixed The variable identified by $identifier
     */
    public function offsetGet($identifier)
    {
        return $this->get($identifier);
    }

    /**
     * @param string $propertyPath
     * @return string
     */
    protected function resolveSubVariableReferences($propertyPath)
    {
        if (strpos($propertyPath, '{') !== false) {
            preg_match_all('/(\{.*\})/', $propertyPath, $matches);
            foreach ($matches[1] as $match) {
                $subPropertyPath = substr($match, 1, -1);
                $propertyPath = str_replace($match, $this->getByPath($subPropertyPath), $propertyPath);
            }
        }
        return $propertyPath;
    }

    /**
     * @param mixed $subject
     * @param string $propertyName
     * @return mixed
     */
    protected function extract($subject, $propertyName)
    {
        if ((is_array($subject) && array_key_exists($propertyName, $subject))
            || ($subject instanceof \ArrayAccess && $subject->offsetExists($propertyName))
        ) {
            return $subject[$propertyName];
        }
        if (is_object($subject)) {
            // Accessor information can be cached per class property if it only relies
            // on class methods that shouldn't change or disappear on runtime
            $cacheIdentifier = get_class($subject) . '->' . $propertyName;
            if (isset($this->objectAccessorCache[$cacheIdentifier])) {
                $accessor = $this->objectAccessorCache[$cacheIdentifier];
            } else {
                $upperCasePropertyName = ucfirst($propertyName);
                $getMethod = 'get' . $upperCasePropertyName;
                $isMethod = 'is' . $upperCasePropertyName;
                $hasMethod = 'has' . $upperCasePropertyName;
                if (method_exists($subject, $getMethod)) {
                    $accessor = $getMethod;
                } elseif (method_exists($subject, $isMethod)) {
                    $accessor = $isMethod;
                } elseif (method_exists($subject, $hasMethod)) {
                    $accessor = $hasMethod;
                }
                if (isset($accessor)) {
                    $this->objectAccessorCache[$cacheIdentifier] = $accessor;
                }
            }

            if (isset($accessor)) {
                return $subject->$accessor();
            }

            // Properties can be dynamic and thus are not covered by the cache
            if (property_exists($subject, $propertyName)) {
                return $subject->$propertyName;
            }
        }
        return null;
    }
}
