<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Variables;

use Psr\Container\ContainerInterface;

/**
 * Class StandardVariableProvider
 */
class StandardVariableProvider implements VariableProviderInterface
{
    protected array $disallowedIdentifiers = ['null', 'true', 'false', '_all'];

    /**
     * Variables stored in context
     *
     * @var mixed
     */
    protected $variables = [];

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

    public function getScopeCopy(array|\ArrayAccess $variables): VariableProviderInterface
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
     */
    public function setSource(mixed $source): void
    {
        $this->variables = $source;
    }

    public function getSource(): mixed
    {
        return $this->variables;
    }

    /**
     * Get every variable provisioned by the VariableProvider
     * implementing the interface. Must return an array or
     * ArrayAccess instance!
     */
    public function getAll(): array|\ArrayAccess
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
    public function add(string $identifier, mixed $value): void
    {
        if (in_array(strtolower($identifier), $this->disallowedIdentifiers)) {
            throw new InvalidVariableIdentifierException('Invalid variable identifier: ' . $identifier, 1723131119);
        }
        if (str_starts_with($identifier, '_')) {
            throw new InvalidVariableIdentifierException('Variable identifiers cannot start with a "_": ' . $identifier, 1756622558);
        }
        $this->variables[$identifier] = $value;
    }

    /**
     * Get a variable from the context.
     *
     * If "_all" is given as identifier, all variables are returned in an array,
     * if one of the other reserved variables are given, their appropriate value
     * they're representing is returned.
     *
     * @return mixed The variable value identified by $identifier
     * @api
     */
    public function get(string $identifier): mixed
    {
        return $this->getByPath($identifier);
    }

    /**
     * Get a variable by dotted path expression, retrieving the
     * variable from nested arrays/objects one segment at a time.
     *
     * @param string $path
     * @return mixed
     */
    public function getByPath(string $path): mixed
    {
        $subject = $this->variables;
        $subVariableReferences = explode('.', $this->resolveSubVariableReferences($path));
        foreach ($subVariableReferences as $pathSegment) {
            if ((is_array($subject) && array_key_exists($pathSegment, $subject))
                || ($subject instanceof \ArrayAccess && $subject->offsetExists($pathSegment))
            ) {
                $subject = $subject[$pathSegment];
                continue;
            }
            if (is_object($subject)) {
                if ($subject instanceof ContainerInterface && $subject->has($pathSegment)) {
                    $subject = $subject->get($pathSegment);
                    continue;
                }
                $upperCasePropertyName = ucfirst($pathSegment);
                $getMethod = 'get' . $upperCasePropertyName;
                if (method_exists($subject, $getMethod)) {
                    $subject = $subject->$getMethod();
                    continue;
                }
                $isMethod = 'is' . $upperCasePropertyName;
                if (method_exists($subject, $isMethod)) {
                    $subject = $subject->$isMethod();
                    continue;
                }
                $hasMethod = 'has' . $upperCasePropertyName;
                if (method_exists($subject, $hasMethod)) {
                    $subject = $subject->$hasMethod();
                    continue;
                }
                if (property_exists($subject, $pathSegment)) {
                    $subject = $subject->$pathSegment;
                    continue;
                }
            }
            return null;
        }
        return $subject;
    }

    /**
     * Remove a variable from context.
     *
     * @param string $identifier The identifier to remove
     * @api
     */
    public function remove(string $identifier): void
    {
        if (array_key_exists($identifier, $this->variables)) {
            unset($this->variables[$identifier]);
        }
    }

    /**
     * Returns an array of all identifiers available in the context.
     *
     * @return string[] Array of identifier strings
     */
    public function getAllIdentifiers(): array
    {
        return array_keys($this->variables);
    }

    /**
     * Checks if this property exists in the VariableContainer.
     *
     * @return bool true if $identifier exists
     * @api
     */
    public function exists(string $identifier): bool
    {
        return array_key_exists($identifier, $this->variables);
    }

    /**
     * Clean up for serializing.
     *
     * @return string[]
     */
    public function __sleep(): array
    {
        return ['variables'];
    }

    /**
     * Adds a variable to the context.
     */
    public function offsetSet(mixed $identifier, mixed $value): void
    {
        $this->add($identifier, $value);
    }

    /**
     * Remove a variable from context.
     */
    public function offsetUnset(mixed $identifier): void
    {
        $this->remove($identifier);
    }

    /**
     * Checks if this property exists in the VariableContainer.
     */
    public function offsetExists(mixed $identifier): bool
    {
        return $this->exists($identifier);
    }

    /**
     * Get a variable from the context.
     */
    public function offsetGet(mixed $identifier): mixed
    {
        return $this->get($identifier);
    }

    protected function resolveSubVariableReferences(string $propertyPath): string
    {
        if (strpos($propertyPath, '{') !== false) {
            // https://www.pcre.org/original/doc/html/pcrepattern.html#SEC1
            // https://stackoverflow.com/questions/546433/regular-expression-to-match-balanced-parentheses
            // https://stackoverflow.com/questions/524548/regular-expression-to-detect-semi-colon-terminated-c-for-while-loops/524624#524624
            // @todo: We're dealing with both *parallel* and *nested* curly braces here. It *might* be better to
            //        substitute the regex with a char-based parser that counts opening vs. closing braces as
            //        mentioned in the links above. Instead, we're currently using a backtracking recursive regex.
            preg_match_all('/{[^}{]*+(?:(?R)[^}{]*)*+}/', $propertyPath, $matches);
            foreach ($matches[0] as $match) {
                $subPropertyPath = substr($match, 1, -1);
                $subPropertyValue = $this->getByPath($subPropertyPath);
                if ($subPropertyValue !== null) {
                    $propertyPath = str_replace($match, (string)$subPropertyValue, $propertyPath);
                }
            }
        }
        return $propertyPath;
    }
}
