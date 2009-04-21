<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\SyntaxTree;

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
 * Node which will call a ViewHelper associated with this node.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ViewHelperNode extends \F3\Fluid\Core\SyntaxTree\AbstractNode {

	/**
	 * Namespace of view helper
	 * @var string
	 */
	protected $viewHelperClassName;

	/**
	 * Arguments of view helper - References to RootNodes.
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * VariableContainer storing the currently available variables.
	 * @var \F3\Fluid\Core\VariableContainer
	 */
	protected $variableContainer;

	/**
	 * Constructor.
	 *
	 * @param string $viewHelperClassName Fully qualified class name of the view helper
	 * @param array $arguments Arguments of view helper - each value is a RootNode.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($viewHelperClassName, array $arguments) {
		$this->viewHelperClassName = $viewHelperClassName;
		$this->arguments = $arguments;
	}

	/**
	 * Get class name of view helper
	 *
	 * @return string Class Name of associated view helper
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperClassName() {
		return $this->viewHelperClassName;
	}

	/**
	 * Call the view helper associated with this object.
	 *
	 * First, it evaluates the arguments of the view helper.
	 *
	 * If the view helper implements \F3\Fluid\Core\Facets\ChildNodeAccessInterface,
	 * it calls setChildNodes(array childNodes) on the view helper.
	 *
	 * Afterwards, checks that the view helper did not leave a variable lying around.
	 *
	 * @param \F3\Fluid\Core\VariableContainer $variableContainer The Variable Container in which the variables are stored
	 * @return object evaluated node after the view helper has been called.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo Handle initializeArguments()
	 * @todo Component manager
	 */
	public function evaluate(\F3\Fluid\Core\VariableContainer $variableContainer) {
		$this->variableContainer = $variableContainer;

		$objectFactory = $variableContainer->getObjectFactory();
		$viewHelper = $objectFactory->create($this->viewHelperClassName);
		$argumentDefinitions = $viewHelper->prepareArguments();

		$contextVariables = $variableContainer->getAllIdentifiers();

		$evaluatedArguments = array();
		$renderMethodParameters = array();
		if (count($argumentDefinitions)) {
			foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
				if (isset($this->arguments[$argumentName])) {
					$argumentValue = $this->arguments[$argumentName];
					$evaluatedArguments[$argumentName] = $this->convertArgumentValue($argumentValue->evaluate($variableContainer), $argumentDefinition->getType());
				} else {
					$evaluatedArguments[$argumentName] = $argumentDefinition->getDefaultValue();
				}
				if ($argumentDefinition->isMethodParameter()) {
					$renderMethodParameters[$argumentName] = $evaluatedArguments[$argumentName];
				}
			}
		}

		$viewHelper->arguments = $objectFactory->create('F3\Fluid\Core\ViewHelperArguments', $evaluatedArguments);
		$viewHelper->variableContainer = $variableContainer;
		$viewHelper->setViewHelperNode($this);

		if ($viewHelper instanceof \F3\Fluid\Core\Facets\ChildNodeAccessInterface) {
			$viewHelper->setChildNodes($this->childNodes);
		}

		$viewHelper->validateArguments();
		$viewHelper->initialize();
		$output = call_user_func_array(array($viewHelper, 'render'), $renderMethodParameters);

		if ($contextVariables != $variableContainer->getAllIdentifiers()) {
			$endContextVariables = $variableContainer->getAllIdentifiers();
			$diff = array_intersect($endContextVariables, $contextVariables);

			throw new \F3\Fluid\Core\RuntimeException('The following context variable has been changed after the view helper "' . $this->viewHelperClassName . '" has been called: ' .implode(', ', $diff), 1236081302);
		}
		return $output;
	}

	/**
	 * Convert argument strings to their equivalents. Needed to handle strings with a boolean meaning.
	 *
	 * @param mixed $value Value to be converted
	 * @param string $type Target type
	 * @return mixed New value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @todo re-check boolean conditions
	 */
	protected function convertArgumentValue($value, $type) {
		if ($type === 'boolean') {
			if (is_string($value)) {
				return (strtolower($value) !== 'false' && !empty($value));
			}
			if (is_array($value) || (is_object($value) && $value instanceof \Countable)) {
				return count($value) > 0;
			}
			if (is_object($value)) {
				return TRUE;
			}
			return FALSE;
		}
		return $value;
	}
}


?>