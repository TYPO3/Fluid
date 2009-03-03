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
abstract class AbstractViewHelper {

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
		$this->argumentDefinitions[] = new \F3\Fluid\Core\ArgumentDefinition($name, $type, $description, $required);
		return $this;
	}

	/**
	 * Get all argument definitions. Used by the framework to get a list of all
	 * arguments registered
	 *
	 * @return array An Array of \F3\Fluid\Core\ArgumentDefinition objects
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getArgumentDefinitions() {
		return $this->argumentDefinitions;
	}

	/**
	 * Sets all needed attributes needed for the rendering. Called by the
	 * framework. Populates $this->viewHelperNode
	 *
	 * @param \F3\Fluid\Core\SyntaxTree\ViewHelperNode $node View Helper node to be set.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
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
	 * Initialize all arguments. You need to override this method and call
	 * $this->registerArgument(...) inside this method, to register all your arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	abstract public function initializeArguments();

	/**
	 * Render method you need to implement for your custom view helper.
	 * Available objects at this point are $this->arguments, and $this->variableContainer.
	 *
	 * Besides, you often need $this->renderChildren().
	 *
	 * @return string rendered string, view helper specific
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	abstract public function render();

}

?>