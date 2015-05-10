<?php
namespace TYPO3\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\Patterns;
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
	 * @return array
	 */
	public function getNamespaces() {
		return $this->namespaces;
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
				sprintf(
					'Namespace "%s" is already registered with another target PHP namespace. Do not re-declare namespaces!',
					$identifier
				),
				1224241246
			);
		}
		$this->namespaces[$identifier] = $phpNamespace;
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
		return array_key_exists($namespaceIdentifier, $this->namespaces);
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

		$name = $this->namespaces[$namespaceIdentifier] . '\\' . $className;

		return $name;
	}

}
