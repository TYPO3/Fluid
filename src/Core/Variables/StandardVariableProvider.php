<?php
namespace TYPO3Fluid\Fluid\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class StandardVariableProvider
 */
class StandardVariableProvider implements VariableProviderInterface
{
    const ACCESSOR_ARRAY = 'array';
    const ACCESSOR_GETTER = 'getter';
    const ACCESSOR_ASSERTER = 'asserter';
    const ACCESSOR_PUBLICPROPERTY = 'public';

    /**
     * Variables stored in context
     *
     * @var mixed
     */
    protected $variables = [];

    /**
     * Internal array of ['className' => ['getFoo', 'getBar'], ...]
     * storing all names of getter methods for a given class.
     *
     * @var array
     */
    protected static $gettersByClassName = [];

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
        return new $className($variables);
    }

    /**
     * Set the source data used by this VariableProvider. The
     * source can be any type, but the type must of course be
     * supported by the VariableProvider itself.
     *
     * @param mixed $source
     * @return void
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
     * @return void
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
     * extraction method names (constants from VariableExtractor)
     * which indicate how each value is extracted.
     *
     * @param string $path
     * @param array $accessors Optional list of accessors (see class constants)
     * @return mixed
     */
    public function getByPath($path, array $accessors = [])
    {
        $subject = $this->variables;
        foreach (explode('.', $this->resolveSubVariableReferences($path)) as $index => $pathSegment) {
            $accessor = isset($accessors[$index]) ? $accessors[$index] : null;
            $subject = $this->extractSingleValue($subject, $pathSegment, $accessor);
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
     * @return void
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
     * @return boolean TRUE if $identifier exists, FALSE otherwise
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
     * @return void
     */
    public function offsetSet($identifier, $value)
    {
        $this->add($identifier, $value);
    }

    /**
     * Remove a variable from context. Throws exception if variable is not found in context.
     *
     * @param string $identifier The identifier to remove
     * @return void
     */
    public function offsetUnset($identifier)
    {
        $this->remove($identifier);
    }

    /**
     * Checks if this property exists in the VariableContainer.
     *
     * @param string $identifier
     * @return boolean TRUE if $identifier exists, FALSE otherwise
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
     * @return array
     */
    public function getAccessorsForPath($propertyPath)
    {
        $subject = $this->variables;
        $accessors = [];
        $propertyPathSegments = explode('.', $propertyPath);
        foreach ($propertyPathSegments as $index => $pathSegment) {
            $accessor = $this->detectAccessor($subject, $pathSegment);
            if ($accessor === null) {
                // Note: this may include cases of sub-variable references. When such
                // a reference is encountered the accessor chain is stopped and new
                // accessors will be detected for the sub-variable and all following
                // path segments since the variable is now fully dynamic.
                break;
            }
            $accessors[] = $accessor;
            $subject = $this->extractSingleValue($subject, $pathSegment);
        }
        return $accessors;
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
     * Extracts a single value from an array or object.
     *
     * @param mixed $subject
     * @param string $propertyName
     * @param string|null $accessor
     * @return mixed
     */
    protected function extractSingleValue($subject, $propertyName, $accessor = null)
    {
        if (!$accessor || !$this->canExtractWithAccessor($subject, $propertyName, $accessor)) {
            $accessor = $this->detectAccessor($subject, $propertyName);
        }
        return $this->extractWithAccessor($subject, $propertyName, $accessor);
    }

    /**
     * Returns TRUE if the data type of $subject is potentially compatible
     * with the $accessor.
     *
     * @param mixed $subject
     * @param string $propertyName
     * @param string $accessor
     * @return boolean
     */
    protected function canExtractWithAccessor($subject, $propertyName, $accessor)
    {
        if ($accessor === self::ACCESSOR_ARRAY) {
            return (is_array($subject) || ($subject instanceof \ArrayAccess && $subject->offsetExists($propertyName)));
        }
        if (!is_object($subject)) {
            // The subject is not an object, none of the next accessors will apply so we return false immediately.
            return false;
        }
        $className = $this->populateGettersByClassName($subject);
        if ($accessor === self::ACCESSOR_GETTER) {
            return static::$gettersByClassName[$className]['get' . ucfirst($propertyName)] ?? false;
        } elseif ($accessor === self::ACCESSOR_ASSERTER) {
            return ($this->isExtractableThroughAsserter($subject, $propertyName));
        } elseif ($accessor === self::ACCESSOR_PUBLICPROPERTY) {
            return (property_exists($subject, $propertyName));
        }
        return false;
    }

    /**
     * @param mixed $subject
     * @param string $propertyName
     * @param string $accessor
     * @return mixed
     */
    protected function extractWithAccessor($subject, $propertyName, $accessor)
    {
        if ($accessor === self::ACCESSOR_ARRAY && is_array($subject) && array_key_exists($propertyName, $subject)
            || $subject instanceof \ArrayAccess && $subject->offsetExists($propertyName)
        ) {
            return $subject[$propertyName];
        } elseif (is_object($subject)) {
            if ($accessor === self::ACCESSOR_GETTER) {
                return call_user_func_array([$subject, 'get' . ucfirst($propertyName)], []);
            } elseif ($accessor === self::ACCESSOR_ASSERTER) {
                return $this->extractThroughAsserter($subject, $propertyName);
            } elseif ($accessor === self::ACCESSOR_PUBLICPROPERTY && property_exists($subject, $propertyName)) {
                return $subject->$propertyName;
            }
        }
        return null;
    }

    /**
     * Detect which type of accessor to use when extracting
     * $propertyName from $subject.
     *
     * @param mixed $subject
     * @param string $propertyName
     * @return string|NULL
     */
    protected function detectAccessor($subject, $propertyName)
    {
        if (is_array($subject) || ($subject instanceof \ArrayAccess && $subject->offsetExists($propertyName))) {
            return self::ACCESSOR_ARRAY;
        }
        if (is_object($subject)) {
            $className = $this->populateGettersByClassName($subject);
            $upperCasePropertyName = ucfirst($propertyName);
            $getter = 'get' . $upperCasePropertyName;
            if (static::$gettersByClassName[$className][$getter] ?? false) {
                return self::ACCESSOR_GETTER;
            }
            if ($this->isExtractableThroughAsserter($subject, $propertyName)) {
                return self::ACCESSOR_ASSERTER;
            }
            if (property_exists($subject, $propertyName)) {
                return self::ACCESSOR_PUBLICPROPERTY;
            }
            if (method_exists($subject, '__call')) {
                return self::ACCESSOR_GETTER;
            }
        }

        return null;
    }

    /**
     * Tests whether a property can be extracted through `is*` or `has*` methods.
     *
     * @param mixed $subject
     * @param string $propertyName
     * @return bool
     */
    protected function isExtractableThroughAsserter($subject, $propertyName)
    {
        $className = $this->populateGettersByClassName($subject);
        $upperCasePropertyName = ucfirst($propertyName);
        return (bool) (static::$gettersByClassName[$className]['is' . $upperCasePropertyName] ?? static::$gettersByClassName[$className]['has' . $upperCasePropertyName] ?? false);
    }

    /**
     * Extracts a property through `is*` or `has*` methods.
     *
     * @param object $subject
     * @param string $propertyName
     * @return mixed
     */
    protected function extractThroughAsserter($subject, $propertyName)
    {
        $className = $this->populateGettersByClassName($subject);
        $upperCasePropertyName = ucfirst($propertyName);
        if (static::$gettersByClassName[$className]['is' . $upperCasePropertyName] ?? false) {
            return call_user_func_array([$subject, 'is' . $upperCasePropertyName], []);
        }

        return call_user_func_array([$subject, 'has' . $upperCasePropertyName], []);
    }

    /**
     * Studies an object and fills the static internal cache of getter method awareness.
     * The method must be called when detecting an accessor for an object and has a very
     * particular reason for existing:
     *
     * - method_exists() will return TRUE even for protected methods
     * - is_callable() will not, but is far too greedy and returns TRUE for *any* method
     *   name if __call is defined on the class.
     * - disregarding Reflection which is horribly slow, get_class_methods is the only
     *   method which returns a list of public methods.
     * - but repeatedly calling get_class_methods() *AND* in_array() is not a sensible
     *   solution.
     * - therefore, an internal cached array is built by class name which allows a null-
     *   coalesce expression to determine if a getter method exists *AND* is public *AND*
     *   is not going to be handled by __call.
     *
     * For ease of use (to avoid repeating get_class calls) the method returns the class
     * name of the instance as returned by get_class($instance).
     *
     * @param object $instance
     * @return string
     */
    protected function populateGettersByClassName($instance)
    {
        $className = get_class($instance);
        if (!isset(static::$gettersByClassName[$className])) {
            $methods = get_class_methods($instance);
            static::$gettersByClassName[$className] = array_combine($methods, $methods);
        }
        return $className;
    }
}
