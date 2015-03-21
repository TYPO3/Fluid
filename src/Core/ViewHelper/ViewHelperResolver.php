<?php
namespace TYPO3\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\ViewHelper\Exception;

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
		'f' => 'TYPO3\\Fluid\\ViewHelpers'
	);

	/**
	 * Filled by calling ignoreNamespace($namespace) any
	 * number of times from the outside. Makes various
	 * functions inside this class error out if the
	 * namespaces which were ignored are referenced by
	 * for example a ViewHelper tag.
	 *
	 * @var array
	 */
	protected $ignoredNamespaces = array();

	/**
	 * List of class names implementing ExpressionNodeInterface
	 * which will be consulted when an expression does not match
	 * any built-in parser expression types.
	 *
	 * @var string
	 */
	protected $expressionNodeTypes = array(
		'TYPO3\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\CastingExpressionNode',
		'TYPO3\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\MathExpressionNode',
		'TYPO3\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\TernaryExpressionNode',
	);

	/**
	 * @return string
	 */
	public function getExpressionNodeTypes() {
		return $this->expressionNodeTypes;
	}

	/**
	 * Registers the given identifier/namespace mapping so that
	 * ViewHelper class names can be properly resolved while parsing
	 *
	 * @param string $identifier
	 * @param string $phpNamespace
	 * @return void
	 * @throws Exception if the specified identifier is already registered
	 */
	public function registerNamespace($identifier, $phpNamespace) {
		if (array_key_exists($identifier, $this->namespaces) && $this->namespaces[$identifier] !== $phpNamespace) {
			throw new Exception(
				sprintf('Namespace identifier "%s" is already registered. Do not re-declare namespaces!', $identifier),
				1224241246
			);
		}
		$this->namespaces[$identifier] = $phpNamespace;
	}

	/**
	 * Mark a namespace as ignored. Attempting to access
	 * namespaces that are ignored will result in errors.
	 *
	 * Filled by the template parser to exclude namespaces,
	 * for example the ones extracted from `xmlns` definitions
	 * but which don't refer to a ViewHelper package.
	 *
	 * @param string $namespace
	 * @return void
	 */
	public function ignoreNamespace($namespace) {
		if (!in_array($namespace, $this->ignoredNamespaces)) {
			$this->ignoredNamespaces[] = $namespace;
		}
	}

	/**
	 * @param array $namespaces
	 * @return void
	 */
	public function setNamespaces(array $namespaces) {
		$this->namespaces = $namespaces;
	}

	/**
	 * Validates the given namespaceIdentifier and throws an exception
	 * if the namespace is unknown and not ignored
	 *
	 * @param string $namespaceIdentifier
	 * @param string $methodIdentifier
	 * @return boolean TRUE if the given namespace is valid, otherwise FALSE
	 * @throws Exception if the given namespace can't be resolved and is not ignored
	 */
	public function isNamespaceValid($namespaceIdentifier, $methodIdentifier) {
		if (array_key_exists($namespaceIdentifier, $this->namespaces)) {
			return TRUE;
		}

		foreach ($this->ignoredNamespaces as $namespaceIdentifierPattern) {
			if (preg_match($namespaceIdentifierPattern, $namespaceIdentifier) === 1) {
				return FALSE;
			}
		}

		throw new Exception(sprintf('Error while resolving a ViewHelper
			The namespace of ViewHelper notation "<%1$s:%2$s.../>" could not be resolved.

			Possible reasons are:
			* you have a spelling error in the viewHelper namespace
			* you forgot to import the namespace using "{namespace %1$s=Some\Package\ViewHelpers}"
			* you\'re trying to use a non-fluid xml namespace, in which case you can use "{namespace %1$s}" to ignore this
			  namespace for fluid rendering', $namespaceIdentifier, $methodIdentifier), 1402521855);
	}

	/**
	 * Resolve an Invoker that will call the ViewHelper given as
	 * argument to render it correctly.
	 *
	 * If any ViewHelper requires special execution to render
	 * correctly, this is the method to override in a custom
	 * ViewHelperResolver to return a different ViewHelperInvoker
	 * for that class and others like it.
	 *
	 * Our default implementation returns the simplest possible
	 * Invoker that only supports the default implementations of
	 * ViewHelper arguments and render methods.
	 *
	 * @param string $viewHelperClassName
	 * @return ViewHelperInvoker
	 */
	public function resolveViewHelperInvoker($viewHelperClassName) {
		return new ViewHelperInvoker($this);
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
			throw new Exception(sprintf(
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
		return new $className();
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

		$name = $this->namespaces[$namespaceIdentifier] . '\\' . $className;

		return $name;
	}

}
