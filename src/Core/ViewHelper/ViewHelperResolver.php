<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\Patterns;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;

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
class ViewHelperResolver {

	/**
	 * Namespaces requested by the template being rendered,
	 * in [shortname => phpnamespace] format.
	 *
	 * @var array
	 */
	protected $namespaces = array(
		'f' => 'TYPO3Fluid\\Fluid\\ViewHelpers'
	);

	/**
	 * @return array
	 */
	public function getNamespaces() {
		return $this->namespaces;
	}

	/**
	 * Registers the given identifier/namespace mapping so that
	 * ViewHelper class names can be properly resolved while parsing.
	 * The namespace can be either a string of a single target PHP
	 * namespace or an array of multiple namespaces (in which case
	 * the resolver treats them with last one having highest priority).
	 *
	 * @param string $identifier
	 * @param string|array $phpNamespace
	 * @return void
	 * @throws Exception if the specified identifier is already registered
	 */
	public function registerNamespace($identifier, $phpNamespace) {
		if (array_key_exists($identifier, $this->namespaces) && $this->namespaces[$identifier] !== $phpNamespace) {
			throw new ParserException(
				sprintf(
					'Namespace "%s" is already registered with another target PHP namespace (%s). Cannot redeclare as %s!',
					$identifier,
					$this->namespaces[$identifier],
					$phpNamespace
				),
				1224241246
			);
		}
		$this->namespaces[$identifier] = $phpNamespace;
	}

	/**
	 * Extend (by overriding) a namespace, making Fluid look in one
	 * or more additional PHP namespaces *before* consulting the
	 * originally registered PHP namespace. Can be used for two main
	 * purposes: one, to add additional ViewHelpers that can also be
	 * used under an existing namespace, and two, to override existing
	 * ViewHelpers under an existing namespace (making Fluid use other
	 * classes for built-in ViewHelpers).
	 *
	 * @param string $identifier
	 * @param string $additionalPhpNamespace
	 * @return void
	 */
	public function extendNamespace($identifier, $additionalPhpNamespace) {
		$this->namespaces[$identifier] = array_merge((array) $this->namespaces[$identifier], array($additionalPhpNamespace));
	}

	/**
	 * Resolves the PHP namespace based on the Fluid xmlns namespace.
	 *
	 * @param string $fluidNamespace
	 * @return string
	 */
	public function resolvePhpNamespaceFromFluidNamespace($fluidNamespace) {
		$namespace = $fluidNamespace;
		$extractedSuffix = substr($fluidNamespace, 0 - strlen(Patterns::NAMESPACESUFFIX));
		if (strpos($fluidNamespace, Patterns::NAMESPACEPREFIX) === 0 && $extractedSuffix === Patterns::NAMESPACESUFFIX) {
			// convention assumed: URL starts with prefix and ends with suffix
			$namespaceSegments = substr($fluidNamespace, strlen(Patterns::NAMESPACEPREFIX));
			$namespace = str_replace('/', '\\', $namespaceSegments);
		}
		return $namespace;
	}

	/**
	 * @param array $namespaces
	 * @return void
	 */
	public function setNamespaces(array $namespaces) {
		$this->namespaces = $namespaces;
	}

	/**
	 * Validates the given namespaceIdentifier and returns FALSE
	 * if the namespace is unknown, causing the tag to be rendered
	 * without processing.
	 *
	 * @param string $namespaceIdentifier
	 * @param string $methodIdentifier
	 * @return boolean TRUE if the given namespace is valid, otherwise FALSE
	 */
	public function isNamespaceValid($namespaceIdentifier, $methodIdentifier) {
		if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
			return FALSE;
		}

		return $this->namespaces[$namespaceIdentifier] !== NULL;
	}

	/**
	 * Validates the given namespaceIdentifier and returns FALSE
	 * if the namespace is unknown and not ignored
	 *
	 * @param string $namespaceIdentifier
	 * @return boolean TRUE if the given namespace is valid, otherwise FALSE
	 */
	public function isNamespaceValidOrIgnored($namespaceIdentifier) {
		if ($this->isNamespaceValid($namespaceIdentifier, '') === TRUE) {
			return TRUE;
		}

		if (array_key_exists($namespaceIdentifier, $this->namespaces)) {
			return TRUE;
		}

		foreach (array_keys($this->namespaces) as $namespace) {
			if (stristr($namespace, '*') === FALSE) {
				continue;
			}
			$pattern = '/' . str_replace(array('.', '*'), array('\\.', '[a-zA-Z0-9\.]*'), $namespace) . '/';
			if (preg_match($pattern, $namespaceIdentifier) === 1) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param string $namespaceIdentifier
	 * @param string $methodIdentifier
	 * @return string|NULL
	 */
	public function resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier) {
		$resolvedViewHelperClassName = $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier);
		$actualViewHelperClassName = implode('\\', array_map('ucfirst', explode('.', $resolvedViewHelperClassName)));
		if (FALSE === class_exists($actualViewHelperClassName) || $actualViewHelperClassName === FALSE) {
			throw new ParserException(sprintf(
				'The ViewHelper "<%s:%s>" could not be resolved.' . chr(10) .
				'Based on your spelling, the system would load the class "%s", however this class does not exist.',
				$namespaceIdentifier, $methodIdentifier, $resolvedViewHelperClassName), 1407060572);
		}
		return $actualViewHelperClassName;
	}

	/**
	 * Can be overridden by custom implementations to change the way
	 * classes are loaded when the class is a ViewHelper - for
	 * example making it possible to use a DI-aware class loader.
	 *
	 * @param string $namespace
	 * @param string $viewHelperShortName
	 */
	public function createViewHelperInstance($namespace, $viewHelperShortName) {
		$className = $this->resolveViewHelperClassName($namespace, $viewHelperShortName);
		return $this->createViewHelperInstanceFromClassName($className);
	}

	/**
	 * Wrapper to create a ViewHelper instance by class name. This is
	 * the final method called when creating ViewHelper classes -
	 * overriding this method allows custom constructors, dependency
	 * injections etc. to be performed on the ViewHelper instance.
	 *
	 * @param string $viewHelperClassName
	 * @return ViewHelperInterface
	 */
	public function createViewHelperInstanceFromClassName($viewHelperClassName) {
		return new $viewHelperClassName();
	}

	/**
	 * Return an array of ArgumentDefinition instances which describe
	 * the arguments that the ViewHelper supports. By default, the
	 * arguments are simply fetched from the ViewHelper - but custom
	 * implementations can if necessary add/remove/replace arguments
	 * which will be passed to the ViewHelper.
	 *
	 * @param ViewHelperInterface $viewHelper
	 * @return ArgumentDefinition[]
	 */
	public function getArgumentDefinitionsForViewHelper(ViewHelperInterface $viewHelper) {
		return $viewHelper->prepareArguments();
	}

	/**
	 * Resolve a viewhelper name.
	 *
	 * @param string $namespaceIdentifier Namespace identifier for the view helper.
	 * @param string $methodIdentifier Method identifier, might be hierarchical like "link.url"
	 * @return string The fully qualified class name of the viewhelper
	 */
	protected function resolveViewHelperName($namespaceIdentifier, $methodIdentifier) {
		$explodedViewHelperName = explode('.', $methodIdentifier);
		if (count($explodedViewHelperName) > 1) {
			$className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
		} else {
			$className = ucfirst($explodedViewHelperName[0]);
		}
		$className .= 'ViewHelper';

		if (is_array($this->namespaces[$namespaceIdentifier])) {
			$namespaces = $this->namespaces[$namespaceIdentifier];
		} else {
			$namespaces = array($this->namespaces[$namespaceIdentifier]);
		}
		do {
			$name = array_pop($namespaces) . '\\' . $className;
		} while (!class_exists($name) && count($namespaces));

		return $name;
	}

}
