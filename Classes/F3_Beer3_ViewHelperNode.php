<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3;

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
class ViewHelperNode extends F3::Beer3::AbstractNode {
	
	/**
	 * Namespace of view helper
	 * @var string
	 */
	protected $viewHelperNamespace;

	/**
	 * Name of view helper
	 * @var string
	 */
	protected $viewHelperName;
	
	/**
	 * Arguments of view helper - References to ArgumentRootNodes.
	 * @var array
	 */
	protected $arguments = array();
	
	/**
	 * Context storing the currently available variables.
	 * @var F3::Beer3::VariableContainer
	 */
	protected $context;
	
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
	public function __construct($viewHelperNamespace, $viewHelperName, F3::Beer3::AbstractViewHelper $viewHelper, $arguments) {
		$this->viewHelperNamespace = $viewHelperNamespace;
		$this->viewHelperName = $viewHelperName;
		$this->arguments = $arguments;
		$this->viewHelper = $viewHelper;
	}

	/**
	 * Get the namespace name of the view helper
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperNamespace() {
		return $this->viewHelperNamespace;
	}
	
	/**
	 * Get name of view helper
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperName() {
		return $this->viewHelperName;
	}
	
	/**
	 * Call the actual view helper associated with this object.
	 * 
	 * Afterwards, checks that the view helper did not leave a variable lying around.
	 * 
	 * @param F3::Beer3::VariableContainer $context The context in which the variables are stored
	 * @return object evaluated node after the view helper has been called.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluate(F3::Beer3::VariableContainer $context) {
		$this->context = $context;
		$contextVariables = $context->getAllIdentifiers();
		$evaluatedArguments = array();
		foreach ($this->arguments as $argumentName => $argumentValue) {
			$evaluatedArguments[$argumentName] = $argumentValue->evaluate($context);
		}
		$this->viewHelper->prepareRendering(new F3::Beer3::Arguments($evaluatedArguments), $this, $context);
		// TODO: Component manager!
		$out = $this->viewHelper->render();
		
		if ($contextVariables != $context->getAllIdentifiers()) {
			$endContextVariables = $context->getAllIdentifiers();
			$diff = array_intersect($endContextVariables, $contextVariables);
			
			throw new F3::Beer3::RuntimeException('The following context variable has been changed after the view helper has been called: ' .implode(', ', $diff));
		}
		return $out;
	}
}


?>