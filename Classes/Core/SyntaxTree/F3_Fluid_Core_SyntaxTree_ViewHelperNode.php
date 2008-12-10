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
 * @version $Id:$
 */
/**
 * Node which will call a ViewHelper associated with this node.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id:$
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
	 * Associated view helper
	 * @var \F3\Fluid\Core\AbstractViewHelper
	 */
	protected $viewHelper;
	
	/**
	 * Constructor.
	 * 
	 * @param string $viewHelperClassName Fully qualified class name of the view helper
	 * @param \F3\Fluid\Core\AbstractViewHelper $viewHelper View helper reference
	 * @param array $arguments Arguments of view helper - each value is a RootNode.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($viewHelperClassName, \F3\Fluid\Core\AbstractViewHelper $viewHelper, $arguments) {
		$this->viewHelperClassName = $viewHelperClassName;
		$this->viewHelper = $viewHelper;
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
	 * If the view helper implements \F3\Fluid\Core\Facets\ChildNodeAccessInterface, it calls setChildNodes(array childNodes)
	 * on the view helper.
	 * 
	 * Afterwards, checks that the view helper did not leave a variable lying around.
	 * 
	 * @param \F3\Fluid\Core\VariableContainer $variableContainer The Variable Container in which the variables are stored
	 * @return object evaluated node after the view helper has been called.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo: Handle initializeArguments()
	 * @todo: Component manager
	 */
	public function evaluate(\F3\Fluid\Core\VariableContainer $variableContainer) {
		$this->viewHelper->initializeArguments();
		
		$this->variableContainer = $variableContainer;
		$contextVariables = $variableContainer->getAllIdentifiers();
		$evaluatedArguments = array();
		foreach ($this->arguments as $argumentName => $argumentValue) {
			$evaluatedArguments[$argumentName] = $argumentValue->evaluate($variableContainer);
		}
		
		// TODO: Component manager!
		$this->viewHelper->arguments = new \F3\Fluid\Core\ViewHelperArguments($evaluatedArguments);
		$this->viewHelper->variableContainer = $variableContainer;
		$this->viewHelper->setViewHelperNode($this);
		
		if ($this->viewHelper instanceof \F3\Fluid\Core\Facets\ChildNodeAccessInterface) {
			$this->viewHelper->setChildNodes($this->childNodes);
		}
		
		$out = $this->viewHelper->render();
		
		if ($contextVariables != $variableContainer->getAllIdentifiers()) {
			$endContextVariables = $variableContainer->getAllIdentifiers();
			$diff = array_intersect($endContextVariables, $contextVariables);
			
			throw new \F3\Fluid\RuntimeException('The following context variable has been changed after the view helper has been called: ' .implode(', ', $diff));
		}
		return $out;
	}
}


?>
