<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\Patterns;

/**
 * Class ViewHelperResolver
 *
 * Responsible for resolving instances of ViewHelpers and for
 * interacting with ViewHelpers; to translate ViewHelper names
 * into actual class names and resolve their ArgumentDefinitions.
 *
 * Replacing this class in for example a framework allows that
 * framework to be responsible for creating ViewHelper instances
 * and detecting possible arguments.
 */
class ViewHelperResolver
{
    protected array $resolvedViewHelperClassNames = [];

    /**
     * Available global namespaces in the current rendering context.
     * Each namespace identifier (like "f") can refer to one or
     * multiple PHP namespaces, specified as an array of class-strings,
     * which will be tested in reverse order:
     *
     * Example:
     *
     *     [
     *         'vendor' => ['Vendor\\A\\ViewHelpers', 'Vendor\\B\\ViewHelpers']
     *     ]
     *
     * A namespace can also be set to "null", which removes
     * all previously defined PHP namespaces and allows the
     * namespace to be used within templates as-is without any Fluid
     * parsing.
     *
     * Example:
     *
     *     [
     *         'vendor' => null
     *     ]
     *
     * A namespace identifier can also include one or multiple
     * "*" wildcard placeholders, which disables Fluid parsing
     * for the matching namespaces.
     *
     * Example:
     *
     *     [
     *         'ven*' => null
     *     ]
     *
     * @var array<string, string[]|null>
     */
    protected array $namespaces = [
        'f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'],
    ];

    /**
     * Available local namespaces in the current rendering context
     * Local namespaces are defined in a template file by using either
     * the {namespace x=Vendor\MyPackage\ViewHelpers} syntax or the
     * xmlns equivalent.
     * Local namespaces are only considered in the current file
     * (template, layout or partial) and don't inherit to any children.
     *
     * @see $namespaces
     * @var array<string, string[]|null>
     */
    protected array $localNamespaces = [];

    /**
     * Namespaces that have neither been defined globally nor locally
     * in the current template file, but in one of its parents. This
     * collection only exists to provide backwards-compatibility in
     * Fluid v4 while still being able to emit deprecation notices.
     *
     * @todo remove this with Fluid v5
     * @see $namespaces
     * @var array<string, string[]|null>
     */
    protected array $inheritedNamespaces = [];

    /**
     * Returns all currently registered namespaces. Note that this includes both
     * global namespaces and local namespaces added from within the current template.
     *
     * @return array<string[]|null>
     */
    public function getNamespaces(): array
    {
        $mergedNamespaces = $this->namespaces;
        foreach ($this->localNamespaces as $identifier => $phpNamespace) {
            if (!array_key_exists($identifier, $mergedNamespaces) || $mergedNamespaces[$identifier] === null) {
                $mergedNamespaces[$identifier] = $phpNamespace === null ? null : (array)$phpNamespace;
            } elseif (is_array($phpNamespace)) {
                $mergedNamespaces[$identifier] = array_unique(array_merge($mergedNamespaces[$identifier], $phpNamespace));
            } elseif (isset($mergedNamespaces[$identifier]) && !in_array($phpNamespace, $mergedNamespaces[$identifier])) {
                $mergedNamespaces[$identifier][] = $phpNamespace;
            }
        }
        return $mergedNamespaces;
    }

    /**
     * Add a PHP namespace where ViewHelpers can be found and give
     * it an alias/identifier. The namespace will be registered
     * globally and can be used in the template and all associated
     * subtemplates (layout, partials).
     *
     * The provided namespace can be either a single namespace or
     * an array of namespaces, as strings. The identifier/alias is
     * always a single, alpha-numeric ASCII string.
     *
     * Calling this method multiple times with different PHP namespaces
     * for the same alias causes that namespace to be *extended*,
     * meaning that the PHP namespace you provide second, third etc.
     * are also used in lookups and are used *first*, so that if any
     * of the namespaces you add contains a class placed and named the
     * same way as one that exists in an earlier namespace, then your
     * class gets used instead of the earlier one.
     *
     * Example:
     *
     * $resolver->addNamespace('my', 'My\Package\ViewHelpers');
     * // Any ViewHelpers under this namespace can now be accessed using for example {my:example()}
     * // Now, assuming you also have an ExampleViewHelper class in a different
     * // namespace and wish to make that ExampleViewHelper override the other:
     * $resolver->addNamespace('my', 'My\OtherPackage\ViewHelpers');
     * // Now, since ExampleViewHelper exists in both places but the
     * // My\OtherPackage\ViewHelpers namespace was added *last*, Fluid
     * // will find and use My\OtherPackage\ViewHelpers\ExampleViewHelper.
     *
     * Alternatively, setNamespaces() can be used to reset and redefine
     * all previously added namespaces - which is great for cases where
     * you need to remove or replace previously added namespaces. Be aware
     * that setNamespaces() also removes the default "f" namespace, so
     * when you use this method you should always include the "f" namespace.
     */
    public function addNamespace(string $identifier, string|array|null $phpNamespace): void
    {
        if (!array_key_exists($identifier, $this->namespaces) || $this->namespaces[$identifier] === null) {
            $this->namespaces[$identifier] = $phpNamespace === null ? null : (array)$phpNamespace;
        } elseif (is_array($phpNamespace)) {
            $this->namespaces[$identifier] = array_unique(array_merge($this->namespaces[$identifier], $phpNamespace));
        } elseif (isset($this->namespaces[$identifier]) && !in_array($phpNamespace, $this->namespaces[$identifier])) {
            $this->namespaces[$identifier][] = $phpNamespace;
        }
    }

    /**
     * Wrapper to allow adding namespaces in bulk *without* first
     * clearing the already added namespaces. Utility method mainly
     * used in compiled templates, where some namespaces can be added
     * from outside and some can be added from compiled values.
     *
     * @internal Only to be used by compiled templates
     * @deprecated Will be removed in v5. Method is not in use anymore.
     */
    public function addNamespaces(array $namespaces): void
    {
        trigger_error('addNamespaces() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        foreach ($namespaces as $identifier => $namespace) {
            $this->addNamespace($identifier, $namespace);
        }
    }

    /**
     * Resolves the PHP namespace based on the Fluid xmlns namespace,
     * which can be either a URL matching the Patterns::NAMESPACEPREFIX
     * and Patterns::NAMESPACESUFFIX rules, or a PHP namespace. When
     * namespace is a PHP namespace it is optional to suffix it with
     * the "\ViewHelpers" segment, e.g. "My\Package" is as valid to
     * use as "My\Package\ViewHelpers" is.
     *
     * @param string $fluidNamespace
     * @return string
     * @deprecated Will be removed in v5. Method is not in use anymore.
     */
    public function resolvePhpNamespaceFromFluidNamespace(string $fluidNamespace): string
    {
        trigger_error('resolvePhpNamespaceFromFluidNamespace() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        $namespace = $fluidNamespace;
        $suffixLength = strlen(Patterns::NAMESPACESUFFIX);
        $phpNamespaceSuffix = str_replace('/', '\\', Patterns::NAMESPACESUFFIX);
        $extractedSuffix = substr($fluidNamespace, 0 - $suffixLength);
        if (strpos($fluidNamespace, Patterns::NAMESPACEPREFIX) === 0 && $extractedSuffix === Patterns::NAMESPACESUFFIX) {
            // convention assumed: URL starts with prefix and ends with suffix
            $namespace = substr($fluidNamespace, strlen(Patterns::NAMESPACEPREFIX));
        }
        $namespace = str_replace('/', '\\', $namespace);
        if (substr($namespace, 0 - strlen($phpNamespaceSuffix)) !== $phpNamespaceSuffix) {
            $namespace .= $phpNamespaceSuffix;
        }
        return $namespace;
    }

    /**
     * Set all global namespaces as an array of ['identifier' => ['Php\Namespace1', 'Php\Namespace2']]
     * namespace definitions. For convenience and legacy support, a
     * format of ['identifier' => 'Only\Php\Namespace'] is allowed,
     * but will internally convert the namespace to an array and
     * allow it to be extended by addNamespace().
     *
     * Note that when using this method the default "f" namespace is
     * also removed and so must be included in $namespaces or added
     * after using addNamespace(). Or, add the PHP namespaces that
     * belonged to "f" as a new alias and use that in your templates.
     *
     * Use getNamespaces() to get an array of currently added namespaces.
     */
    public function setNamespaces(array $namespaces): void
    {
        $this->namespaces = [];
        foreach ($namespaces as $identifier => $phpNamespace) {
            $this->namespaces[$identifier] = $phpNamespace === null ? null : (array)$phpNamespace;
        }
    }

    /**
     * @internal
     */
    public function addLocalNamespace(string $identifier, ?string $phpNamespace): void
    {
        if ($phpNamespace === null) {
            $this->localNamespaces[$identifier] = null;
        } else {
            $this->localNamespaces[$identifier] ??= [];
            $this->localNamespaces[$identifier][] = $phpNamespace;
        }
    }

    /**
     * @param array<string, string[]|null> $namespaces
     * @internal
     */
    public function setLocalNamespaces(array $namespaces): void
    {
        $this->localNamespaces = $namespaces;
    }

    /**
     * @return array<string, string[]>
     * @internal
     */
    public function getLocalNamespaces(): array
    {
        return $this->localNamespaces;
    }

    /**
     * @param array<string, string[]|null> $namespaces
     * @internal
     * @todo remove this with Fluid v5
     */
    protected function addInheritedNamespaces(array $namespaces): void
    {
        foreach ($namespaces as $identifier => $phpNamespaces) {
            if ($phpNamespaces === null) {
                $this->inheritedNamespaces[$identifier] = null;
            } else {
                $this->inheritedNamespaces[$identifier] = array_unique(array_merge(
                    $this->inheritedNamespaces[$identifier] ?? [],
                    $phpNamespaces,
                ));
            }
        }
    }

    /**
     * Creates a copy of the ViewHelperResolver that still contains all globally
     * registered ViewHelper namespaces, but no local namespaces.
     *
     * @internal
     */
    public function getScopedCopy(): ViewHelperResolver
    {
        $copy = clone $this;
        // @todo remove this with Fluid v5
        $copy->addInheritedNamespaces($copy->getLocalNamespaces());
        $copy->setLocalNamespaces([]);
        return $copy;
    }

    /**
     * Validates the given namespaceIdentifier and returns false
     * if the namespace is unknown, causing the tag to be rendered
     * without processing.
     *
     * @return bool true if the given namespace is valid
     */
    public function isNamespaceValid(string $namespaceIdentifier): bool
    {
        $namespaces = $this->getNamespaces();
        if (isset($namespaces[$namespaceIdentifier])) {
            return true;
        }

        // Check ViewHelper namespaces that were inherited from parent templates
        // as a fallback
        // @todo remove with Fluid v5
        if (isset($this->inheritedNamespaces[$namespaceIdentifier])) {
            throw new InheritedNamespaceException();
        }

        return false;
    }

    /**
     * Validates the given namespaceIdentifier and returns false
     * if the namespace is unknown and not ignored
     *
     * @param string $namespaceIdentifier
     * @return bool true if the given namespace is valid
     * @deprecated Will be removed in v5. Use combination of isNamespaceIgnored() and isNamespaceValid() instead.
     */
    public function isNamespaceValidOrIgnored(string $namespaceIdentifier): bool
    {
        trigger_error('isNamespaceValidOrIgnored() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        if ($this->isNamespaceValid($namespaceIdentifier) === true) {
            return true;
        }

        if (array_key_exists($namespaceIdentifier, $this->getNamespaces())) {
            return true;
        }

        if ($this->isNamespaceIgnored($namespaceIdentifier)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $namespaceIdentifier
     * @return bool
     */
    public function isNamespaceIgnored(string $namespaceIdentifier): bool
    {
        $namespaces = $this->getNamespaces();
        if (array_key_exists($namespaceIdentifier, $namespaces)) {
            return $namespaces[$namespaceIdentifier] === null;
        }
        foreach (array_keys($namespaces) as $existingNamespaceIdentifier) {
            if (strpos($existingNamespaceIdentifier, '*') === false) {
                continue;
            }
            $pattern = '/' . str_replace(['.', '*'], ['\\.', '[a-zA-Z0-9\.]*'], $existingNamespaceIdentifier) . '/';
            if (preg_match($pattern, $namespaceIdentifier) === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resolves a ViewHelper class name by namespace alias and
     * Fluid-format identity, e.g. "f" and "format.htmlspecialchars".
     *
     * Looks in all PHP namespaces which have been added for the
     * provided alias, starting in the last added PHP namespace. If
     * a ViewHelper class exists in multiple PHP namespaces Fluid
     * will detect and use whichever one was added last.
     *
     * If no ViewHelper class can be detected in any of the added
     * PHP namespaces a Fluid Parser Exception is thrown.
     *
     * @throws ParserException
     */
    public function resolveViewHelperClassName(string $namespaceIdentifier, string $methodIdentifier): string
    {
        if (!isset($this->resolvedViewHelperClassNames[$namespaceIdentifier][$methodIdentifier])) {
            $resolvedViewHelperClassName = $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier);
            $actualViewHelperClassName = implode('\\', array_map('ucfirst', explode('.', $resolvedViewHelperClassName)));
            if (!class_exists($actualViewHelperClassName)) {
                throw new ParserException(sprintf(
                    'The ViewHelper "<%s:%s>" could not be resolved.' . chr(10) .
                    'Based on your spelling, the system would load the class "%s", however this class does not exist.',
                    $namespaceIdentifier,
                    $methodIdentifier,
                    $resolvedViewHelperClassName,
                ), 1407060572);
            }
            $this->resolvedViewHelperClassNames[$namespaceIdentifier][$methodIdentifier] = $actualViewHelperClassName;
        }
        return $this->resolvedViewHelperClassNames[$namespaceIdentifier][$methodIdentifier];
    }

    /**
     * Can be overridden by custom implementations to change the way
     * classes are loaded when the class is a ViewHelper - for
     * example making it possible to use a DI-aware class loader.
     */
    public function createViewHelperInstance(string $namespace, string $viewHelperShortName): ViewHelperInterface
    {
        $className = $this->resolveViewHelperClassName($namespace, $viewHelperShortName);
        return $this->createViewHelperInstanceFromClassName($className);
    }

    /**
     * Wrapper to create a ViewHelper instance by class name. This is
     * the final method called when creating ViewHelper classes -
     * overriding this method allows custom constructors, dependency
     * injections etc. to be performed on the ViewHelper instance.
     */
    public function createViewHelperInstanceFromClassName(string $viewHelperClassName): ViewHelperInterface
    {
        return new $viewHelperClassName();
    }

    /**
     * Return an array of ArgumentDefinition instances which describe
     * the arguments that the ViewHelper supports. By default, the
     * arguments are simply fetched from the ViewHelper - but custom
     * implementations can if necessary add/remove/replace arguments
     * which will be passed to the ViewHelper.
     *
     * @return ArgumentDefinition[]
     */
    public function getArgumentDefinitionsForViewHelper(ViewHelperInterface $viewHelper): array
    {
        return $viewHelper->prepareArguments();
    }

    /**
     * Resolve a viewhelper name.
     *
     * @param string $namespaceIdentifier Namespace identifier for the view helper.
     * @param string $methodIdentifier Method identifier, might be hierarchical like "link.url"
     * @return string The fully qualified class name of the viewhelper
     */
    protected function resolveViewHelperName(string $namespaceIdentifier, string $methodIdentifier): string
    {
        $explodedViewHelperName = explode('.', $methodIdentifier);
        if (count($explodedViewHelperName) > 1) {
            $className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
        } else {
            $className = ucfirst($explodedViewHelperName[0]);
        }
        $className .= 'ViewHelper';

        if (isset($this->getNamespaces()[$namespaceIdentifier])) {
            foreach (array_reverse($this->getNamespaces()[$namespaceIdentifier]) as $namespace) {
                $name = rtrim((string)$namespace, '\\') . '\\' . $className;
                if (class_exists($name)) {
                    return $name;
                }
            }
        }

        // Check ViewHelper namespaces that were inherited from parent templates
        // as a fallback
        // @todo remove this with Fluid v5
        if (isset($this->inheritedNamespaces[$namespaceIdentifier])) {
            foreach (array_reverse($this->inheritedNamespaces[$namespaceIdentifier]) as $namespace) {
                $fallbackName = rtrim((string)$namespace, '\\') . '\\' . $className;
                if (class_exists($fallbackName)) {
                    return $fallbackName;
                }
            }
        }

        // To stay consistent with previous API, we return the last failed class match to be used in
        // the exception message. We don't include inherited namespaces because this would encourage
        // usage of invalid syntax.
        return $name ?? '';
    }
}
