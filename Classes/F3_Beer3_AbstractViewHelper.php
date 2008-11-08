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
 * The abstract base class for all view helpers
 *
 * @package Beer3
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
abstract class AbstractViewHelper {
	
	/**
	 * Stores all F3::Beer3::ArgumentDefinition
	 * @var array
	 */
	private $argumentDefinitions = array();
	
	/**
	 * Arguments accessor
	 * @var F3::Beer3::Arguments
	 */
	protected $arguments;
	
	/**
	 * Current view helper node
	 * @var F3::Beer3::ViewHelperNode
	 */
	protected $viewHelperNode;
	
	/**
	 * Current variable container reference
	 * @var F3::Beer3::VariableContainer
	 */
	protected $variableContainer;
	
	/**
	 * Register a new argument. Call this method from the implementing ViewHelper class inside the initializeArguments() method.
	 *
	 * @param string $name Name of the argument
	 * @param string $type Type of the argument
	 * @param string $description Description of the argument
	 * @param boolean $isOptional If TRUE, argument is optional. Defaults to FALSE.
	 * @return $this this object to allow chaining.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function registerArgument($name, $type, $description, $isOptional = FALSE) {
		// TODO -> use component manager here!!
		$this->argumentDefinitions[] = new F3::Beer3::ArgumentDefinition($name, $type, $description, $isOptional);
		return $this;
	}
	
	/**
	 * Get all argument definitions. Used by the framework to get a list of all arguments registered
	 *
	 * @return array An Array of F3::Beer3::ArgumentDefinition objects
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getArgumentDefinitions() {
		return $this->argumentDefinitions;
	}
	
	/**
	 * Sets all needed attributes needed for the rendering. Called by the framework.
	 * Populates $this->arguments, $this->viewHelperNode, and $this->varibleContainer.
	 *
	 * @param Arguments $arguments
	 * @param ViewHelperNode $node
	 * @param VariableContainer $variableContainer
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function prepareRendering(F3::Beer3::Arguments $arguments, F3::Beer3::Core::SyntaxTree::ViewHelperNode $node, F3::Beer3::Core::VariableContainer $variableContainer) {
		$this->arguments = $arguments;
		$this->viewHelperNode = $node;
		$this->variableContainer = $variableContainer;
	}

	/**
	 * Helper method which triggers the rendering of everything between the opening and the closing tag.
	 * 
	 * @return string The finally rendered string.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function renderChildren() {
		return $this->viewHelperNode->renderChildNodes();
	}
	
	/**
	 * Initialize all arguments. You need to override this method and call
	 * $this->registerArgument() inside this method, to register all your arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	abstract public function initializeArguments();
	
	/**
	 * Render method you need to implement for your custom view helper.
	 * Available objects at this point are $this->arguments, and $this->variableContainer.
	 *
	 * @return string rendered string, view helper specific
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	abstract public function render();
}


?>