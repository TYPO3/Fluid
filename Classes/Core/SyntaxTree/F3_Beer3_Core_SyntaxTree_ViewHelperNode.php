<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::Core::SyntaxTree;

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
 * @package Beer3
 * @version $Id:$
 */
/**
 * Dynamic node
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ViewHelperNode extends F3::Beer3::Core::SyntaxTree::AbstractNode {
	
	/**
	 * Namespace of view helper
	 * @var string
	 */
	protected $viewHelperClassName;

	/**
	 * Arguments of view helper - References to ArgumentRootNodes.
	 * @var array
	 */
	protected $arguments = array();
	
	/**
	 * VariableContainer storing the currently available variables.
	 * @var F3::Beer3::Core::VariableContainer
	 */
	protected $variableContainer;
	
	/**
	 * Associated view helper
	 * @var F3::Beer3::AbstractViewHelper
	 */
	protected $viewHelper;
	
	/**
	 * Constructor.
	 * 
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($viewHelperClassName, F3::Beer3::Core::AbstractViewHelper $viewHelper, $arguments) {
		$this->viewHelperClassName = $viewHelperClassName;
		$this->viewHelper = $viewHelper;
		$this->arguments = $arguments;
	}
	
	/**
	 * Get class name of view helper
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperClassName() {
		return $this->viewHelperClassName;
	}
	
	/**
	 * Call the actual view helper associated with this object.
	 * 
	 * If the view helper implements Facets::ChildNodeAccessInterface, it calls setChildNodes(array childNodes)
	 * on the view helper.
	 * 
	 * Afterwards, checks that the view helper did not leave a variable lying around.
	 * 
	 * @param F3::Beer3::VariableContainer $variableContainer The Variable Container in which the variables are stored
	 * @return object evaluated node after the view helper has been called.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluate(F3::Beer3::Core::VariableContainer $variableContainer) {
		$this->viewHelper->initializeArguments();
		
		$this->variableContainer = $variableContainer;
		$contextVariables = $variableContainer->getAllIdentifiers();
		$evaluatedArguments = array();
		foreach ($this->arguments as $argumentName => $argumentValue) {
			$evaluatedArguments[$argumentName] = $argumentValue->evaluate($variableContainer);
		}
		
		// TODO: Component manager!
		$this->viewHelper->arguments = new F3::Beer3::Core::ViewHelperArguments($evaluatedArguments);
		$this->viewHelper->variableContainer = $variableContainer;
		$this->viewHelper->setViewHelperNode($this);
		
		if ($this->viewHelper instanceof F3::Beer3::Core::Facets::ChildNodeAccessInterface) {
			$this->viewHelper->setChildNodes($this->childNodes);
		}
		
		$out = $this->viewHelper->render();
		
		if ($contextVariables != $variableContainer->getAllIdentifiers()) {
			$endContextVariables = $variableContainer->getAllIdentifiers();
			$diff = array_intersect($endContextVariables, $contextVariables);
			
			throw new F3::Beer3::RuntimeException('The following context variable has been changed after the view helper has been called: ' .implode(', ', $diff));
		}
		return $out;
	}
}


?>