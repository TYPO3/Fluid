<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AtomNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ReferenceNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * @var array
     */
    protected $resolvedViewHelperClassNames = [];

    /**
     * Atom paths indexed by namespace, in
     * [shortname => [path1, path2, ...]] format.
     * @var array
     */
    protected $atoms = [];

    /**
     * Namespaces requested by the template being rendered,
     * in [shortname => [phpnamespace1, phpnamespace2, ...]] format.
     *
     * @var array
     */
    protected $namespaces = [
        'f' => ['TYPO3Fluid\\Fluid\\ViewHelpers']
    ];

    /**
     * @var array
     */
    protected $aliases = [
        'html' => ['f', 'html'],
        'raw' => ['f', 'format.raw'],
    ];

    public function __construct(RenderingContextInterface $renderingContext)
    {
        $this->renderingContext = $renderingContext;
    }

    public function addAtomPath(string $namespace, string $path): void
    {
        if (!in_array($path, $this->atoms[$namespace] ?? [], true)) {
            $this->atoms[$namespace][] = $path;
        }
    }

    /**
     * Add all Atom paths as array-in-array, with the first level key
     * being the namespace and the value being an array of paths.
     *
     * Example:
     *
     * $resolver->addAtomPaths(
     *   [
     *     'my' => [
     *       'path/first/',
     *       'path/second/',
     *     ],
     *     'other' => [
     *       'path/third/',
     *     ],
     *   ]
     * );
     *
     * @param iterable|string[][] $paths
     */
    public function addAtomPaths(iterable $paths): void
    {
        foreach ($paths as $namespace => $collection) {
            foreach ($collection as $path) {
                $this->addAtomPath($namespace, $path);
            }
        }
    }

    public function resolveAtom(string $namespace, string $name): ComponentInterface
    {
        $file = $this->resolveAtomFile($namespace, $name);
        if (!$file) {
            $paths = empty($this->atoms[$namespace]) ? 'none' : implode(', ', $this->atoms[$namespace]);
            throw new ChildNotFoundException(
                'Atom "' . $namespace . ':' . $name . '" could not be resolved. We looked in: ' . $paths,
                1564404340
            );
        }
        /** @var EntryNode $atom */
        $atom = $this->renderingContext->getTemplateParser()->parseFile($file);
        return $atom->setName($namespace . ':' . $name);
    }

    public function resolveAtomFile(string $namespace, string $name): ?string
    {
        if (!isset($this->atoms[$namespace])) {
            return null;
        }
        $expectedFileParts = explode('.', $name);
        foreach (array_reverse($this->atoms[$namespace]) as $path) {
            $parts = $expectedFileParts;
            $subPath = $path;
            while ($expectedFilePart = array_shift($parts)) {
                $subPath .= '/' . $expectedFilePart;
                if (!is_dir($subPath)) {
                    break;
                }
            }
            $filePathAndFilename = $subPath . '.html';
            if (file_exists($filePathAndFilename)) {
                return $filePathAndFilename;
            }
        }
        return null;
    }

    /**
     * @return array|string[][]
     */
    public function getAtoms(): array
    {
        return $this->atoms;
    }

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Adds an alias of a ViewHelper, allowing you to call for example
     *
     * @param string $alias
     * @param string $namespace
     * @param string $identifier
     */
    public function addViewHelperAlias(string $alias, string $namespace, string $identifier)
    {
        $this->aliases[$alias] = [$namespace, $identifier];
    }

    public function isAliasRegistered(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Add a PHP namespace where ViewHelpers can be found and give
     * it an alias/identifier.
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
     *
     * @param string $identifier
     * @param string|array|null $phpNamespace
     * @return void
     */
    public function addNamespace(string $identifier, $phpNamespace): void
    {
        if (!array_key_exists($identifier, $this->namespaces) || $this->namespaces[$identifier] === null) {
            $this->namespaces[$identifier] = $phpNamespace === null ? null : (array) $phpNamespace;
        } elseif (is_array($phpNamespace)) {
            $this->namespaces[$identifier] = array_unique(array_merge($this->namespaces[$identifier], $phpNamespace));
        } elseif (isset($this->namespaces[$identifier]) && !in_array($phpNamespace, $this->namespaces[$identifier], true)) {
            $this->namespaces[$identifier][] = $phpNamespace;
        }
        $this->resolvedViewHelperClassNames = [];
    }

    /**
     * Wrapper to allow adding namespaces in bulk *without* first
     * clearing the already added namespaces. Utility method mainly
     * used in compiled templates, where some namespaces can be added
     * from outside and some can be added from compiled values.
     *
     * @param array $namespaces
     * @return void
     */
    public function addNamespaces(array $namespaces): void
    {
        foreach ($namespaces as $identifier => $namespace) {
            $this->addNamespace($identifier, $namespace);
        }
    }

    public function removeNamespace(string $identifier, $phpNamespace): void
    {
        if (($key = array_search($phpNamespace, $this->namespaces[$identifier], true)) !== false) {
            unset($this->namespaces[$identifier][$key]);
            if (empty($this->namespaces[$identifier])) {
                unset($this->namespaces[$identifier]);
            }
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
     */
    public function resolvePhpNamespaceFromFluidNamespace(string $fluidNamespace): string
    {
        $prefix = 'http://typo3.org/ns/';
        $suffix = '/ViewHelpers';
        $namespace = $fluidNamespace;
        $suffixLength = strlen($suffix);
        $phpNamespaceSuffix = str_replace('/', '\\', $suffix);
        $extractedSuffix = substr($fluidNamespace, 0 - $suffixLength);
        if (strpos($fluidNamespace, $prefix) === 0 && $extractedSuffix === $suffix) {
            // convention assumed: URL starts with prefix and ends with suffix
            $namespace = substr($fluidNamespace, strlen($prefix));
        }
        $namespace = str_replace('/', '\\', $namespace);
        if (substr($namespace, 0 - strlen($phpNamespaceSuffix)) !== $phpNamespaceSuffix) {
            $namespace .= $phpNamespaceSuffix;
        }
        return $namespace;
    }

    /**
     * Set all namespaces as an array of ['identifier' => ['Php\Namespace1', 'Php\Namespace2']]
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
     *
     * @param array $namespaces
     * @return void
     */
    public function setNamespaces(array $namespaces): void
    {
        $this->namespaces = [];
        foreach ($namespaces as $identifier => $phpNamespace) {
            $this->namespaces[$identifier] = $phpNamespace === null ? null : (array) $phpNamespace;
        }
    }

    /**
     * Validates the given namespaceIdentifier and returns FALSE
     * if the namespace is unknown, causing the tag to be rendered
     * without processing.
     *
     * @param string $namespaceIdentifier
     * @return boolean TRUE if the given namespace is valid, otherwise FALSE
     */
    public function isNamespaceValid(string $namespaceIdentifier): bool
    {
        if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
            return false;
        }

        return $this->namespaces[$namespaceIdentifier] !== null && $namespaceIdentifier !== 'this';
    }

    /**
     * Validates the given namespaceIdentifier and returns FALSE
     * if the namespace is unknown and not ignored
     *
     * @param string $namespaceIdentifier
     * @return boolean TRUE if the given namespace is valid, otherwise FALSE
     */
    public function isNamespaceValidOrIgnored(string $namespaceIdentifier): bool
    {
        if ($this->isNamespaceValid($namespaceIdentifier)) {
            return true;
        }

        if (array_key_exists($namespaceIdentifier, $this->namespaces) || array_key_exists($namespaceIdentifier, $this->atoms)) {
            return true;
        }
        return $this->isNamespaceIgnored($namespaceIdentifier);
    }

    /**
     * @param string $namespaceIdentifier
     * @return boolean
     */
    public function isNamespaceIgnored(string $namespaceIdentifier): bool
    {
        if (array_key_exists($namespaceIdentifier, $this->namespaces) && $this->namespaces[$namespaceIdentifier] === null) {
            return true;
        }
        foreach (array_keys($this->namespaces) as $existingNamespaceIdentifier) {
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
     * @param string|null $namespaceIdentifier
     * @param string $methodIdentifier
     * @return string|null
     * @throws Exception
     */
    public function resolveViewHelperClassName(?string $namespaceIdentifier, string $methodIdentifier): ?string
    {
        if ($namespaceIdentifier === 'this') {
            return ReferenceNode::class;
        }
        if (empty($namespaceIdentifier) && isset($this->aliases[$methodIdentifier])) {
            list ($namespaceIdentifier, $methodIdentifier) = $this->aliases[$methodIdentifier];
        }
        if (!isset($this->resolvedViewHelperClassNames[$namespaceIdentifier][$methodIdentifier])) {
            $actualViewHelperClassName = false;

            $explodedViewHelperName = explode('.', $methodIdentifier);
            $className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
            $className .= 'ViewHelper';

            if (!empty($this->namespaces[$namespaceIdentifier])) {
                foreach (array_reverse($this->namespaces[$namespaceIdentifier]) as $namespace) {
                    $name = $namespace . '\\' . $className;
                    if (class_exists($name)) {
                        $actualViewHelperClassName = $name;
                        break;
                    }
                }
            }

            if ($actualViewHelperClassName === false) {

                // If namespace and method match an Atom, return AtomNode's class name. Otherwise, error out.
                if ($this->resolveAtomFile($namespaceIdentifier, $methodIdentifier)) {
                    return AtomNode::class;
                }

                throw new Exception(sprintf(
                    'A Component named "<%s:%s>" could not be resolved.' . chr(10) .
                    'We looked in the following namespaces: %s.',
                    $namespaceIdentifier,
                    $methodIdentifier,
                    implode(', ', $this->namespaces[$namespaceIdentifier] ?? ['none'])
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
     *
     * If null is passed as namespace, only registered ViewHelper
     * aliases are checked against the $viewHelperShortName.
     *
     * @param string|null $namespace
     * @param string $viewHelperShortName
     * @return ComponentInterface
     */
    public function createViewHelperInstance(?string $namespace, string $viewHelperShortName): ComponentInterface
    {
        if ($namespace === 'this') {
            return new ReferenceNode($viewHelperShortName);
        }
        if (!empty($namespace) && isset($this->atoms[$namespace])) {
            $atomFile = $this->resolveAtomFile($namespace, $viewHelperShortName);
            if ($atomFile) {
                $node = new AtomNode();
                $node->setFile($atomFile);
                $node->setName($namespace . ':' . $viewHelperShortName);
                $node->setArguments(clone $this->renderingContext->getTemplateParser()->parseFile($atomFile)->getArguments());
                return $node;
            }
        }
        $className = $this->resolveViewHelperClassName($namespace, $viewHelperShortName);
        $instance = $this->createViewHelperInstanceFromClassName($className);
        return $instance;
    }

    /**
     * Wrapper to create a ViewHelper instance by class name. This is
     * the final method called when creating ViewHelper classes -
     * overriding this method allows custom constructors, dependency
     * injections etc. to be performed on the ViewHelper instance.
     *
     * @param string $viewHelperClassName
     * @return ComponentInterface
     */
    public function createViewHelperInstanceFromClassName(string $viewHelperClassName)
    {
        return new $viewHelperClassName();
    }
}
