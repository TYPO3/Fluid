<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 */

/**
 * The abstract base class for all view helpers.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
abstract class AbstractViewHelper implements \F3\Fluid\Core\ViewHelperInterface {

	/**
	 * Stores all \F3\Fluid\ArgumentDefinition instances
	 * @var array
	 */
	private $argumentDefinitions = array();

	/**
	 * Current view helper node
	 * @var \F3\Fluid\Core\SyntaxTree\ViewHelperNode
	 */
	private $viewHelperNode;

	/**
	 * Arguments accessor. Must be public, because it is set from the framework.
	 * @var \F3\Fluid\Core\ViewHelperArguments
	 */
	public $arguments;

	/**
	 * Current variable container reference. Must be public because it is set by the framework
	 * @var \F3\Fluid\Core\VariableContainer
	 */
	public $variableContainer;

	/**
	 * Validator resolver
	 * @var \F3\FLOW3\Validation\ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * Reflection service
	 * @var \F3\Fluid\Service\ParameterReflectionService
	 */
	protected $parameterReflectionService;

	/**
	 * Inject a validator resolver
	 * @param \F3\FLOW3\Validation\ValidatorResolver $validatorResolver Validator Resolver
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function injectValidatorResolver(\F3\FLOW3\Validation\ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * Inject a Reflection service
	 * @param \F3\Fluid\Service\ParameterReflectionService $parameterReflectionService Reflection service
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function injectParameterReflectionService(\F3\Fluid\Service\ParameterReflectionService $parameterReflectionService) {
		$this->parameterReflectionService = $parameterReflectionService;
	}

	/**
	 * Register a new argument. Call this method from your ViewHelper subclass
	 * inside the initializeArguments() method.
	 *
	 * @param string $name Name of the argument
	 * @param string $type Type of the argument
	 * @param string $description Description of the argument
	 * @param boolean $required If TRUE, argument is required. Defaults to FALSE.
	 * @return \F3\Fluid\Core\AbstractViewHelper $this, to allow chaining.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo Component manager usage!
	 */
	protected function registerArgument($name, $type, $description, $required = FALSE) {
		$this->argumentDefinitions[$name] = new \F3\Fluid\Core\ArgumentDefinition($name, $type, $description, $required);
		return $this;
	}

	/**
	 * Sets all needed attributes needed for the rendering. Called by the
	 * framework. Populates $this->viewHelperNode
	 *
	 * @param \F3\Fluid\Core\SyntaxTree\ViewHelperNode $node View Helper node to be set.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	final public function setViewHelperNode(\F3\Fluid\Core\SyntaxTree\ViewHelperNode $node) {
		$this->viewHelperNode = $node;
	}

	/**
	 * Helper method which triggers the rendering of everything between the
	 * opening and the closing tag.
	 *
	 * @return string The finally rendered string.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function renderChildren() {
		return $this->viewHelperNode->renderChildNodes();
	}

	/**
	 * Initialize all arguments and return them
	 *
	 * @return array Array of F3\Fluid\Core\ArgumentDefinition instances.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function prepareArguments() {
		$this->registerRenderMethodArguments();
		$this->initializeArguments();
		return $this->argumentDefinitions;
	}

	/**
	 * Register method arguments for "render" by analysing the doc comment above.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	private function registerRenderMethodArguments() {

		$parameters = $this->parameterReflectionService->getMethodParameters(get_class($this), 'render');
		foreach ($parameters as $parameter) {
			$this->registerArgument($parameter['name'], $parameter['dataType'], $parameter['description'], $parameter['required']);
		}
	}

	/**
	 * Validate arguments, and throw exception if arguments do not validate.
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function validateArguments() {
		$argumentDefinitions = $this->prepareArguments();
		if (!count($argumentDefinitions)) return;

		foreach ($argumentDefinitions as $argumentName => $registeredArgument) {
			if ($this->arguments->offsetExists($argumentName)) {
				$type = $registeredArgument->getType();
				if ($type === 'array') {
					if (!is_array($this->arguments[$argumentName])) {
						throw new \F3\Fluid\Core\RuntimeException('An argument "' . $argumentName . '" was registered with type array, but it is no array.', 1237900529);
					}
				} else {
					$validator = $this->validatorResolver->getValidator($type);
					if (is_null($validator)) {
						throw new \F3\Fluid\Core\RuntimeException('No validator found for argument name "' . $argumentName . '" with type "' . $type . '".', 1237900534);
					}
					$errors = new \F3\FLOW3\Validation\Errors();

					if (!$validator->isValid($this->arguments[$argumentName], $errors)) {
						throw new \F3\Fluid\Core\RuntimeException('Validation for argument name "' . $argumentName . '" FAILED.', 1237900686);
					}
				}
			}
		}
	}

	/**
	 * Initialize all arguments. You need to override this method and call
	 * $this->registerArgument(...) inside this method, to register all your arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
	}

	/**
	 * Render method you need to implement for your custom view helper.
	 * Available objects at this point are $this->arguments, and $this->variableContainer.
	 *
	 * Besides, you often need $this->renderChildren().
	 *
	 * @return string rendered string, view helper specific
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	//abstract public function render();

}

?>