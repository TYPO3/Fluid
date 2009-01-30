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
 * @version $Id:$
 */
/**
 * Stores all information relevant for one parsing pass - that is, the root node,
 * and the current stack of open nodes (nodeStack) and a variable container used for PostParseFacets.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ParsingState implements \F3\Fluid\Core\ParsedTemplateInterface {

	/**
	 * Root node reference
	 * @var \F3\Fluid\Core\SyntaxTree\RootNode
	 */
	protected $rootNode;

	/**
	 * Array of node references currently open.
	 * @var array
	 */
	protected $nodeStack = array();

	/**
	 * Variable container where ViewHelpers implementing the PostParseFacet can store things in.
	 * @var \F3\Fluid\Core\VariableContainer
	 */
	protected $variableContainer;

	/**
	 * Injects a variable container. ViewHelpers implementing the PostParse Facet can store information inside this variableContainer.
	 *
	 * @param \F3\Fluid\Core\VariableContainer $variableContainer
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectVariableContainer(\F3\Fluid\Core\VariableContainer $variableContainer) {
		$this->variableContainer = $variableContainer;
	}

	/**
	 * Set root node of this parsing state
	 *
	 * @param \F3\Fluid\Core\SyntaxTree\AbstractNode $rootNode
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo RENAME THIS!!
	 */
	public function setRootNode(\F3\Fluid\Core\SyntaxTree\AbstractNode $rootNode) {
		$this->rootNode = $rootNode;
	}

	/**
	 * Get root node of this parsing state.
	 *
	 * @return \F3\Fluid\Core\SyntaxTree\AbstractNode The root node
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getRootNode() {
		return $this->rootNode;
	}

	/**
	 * Push a node to the node stack. The node stack holds all currently open templating tags.
	 *
	 * @param \F3\Fluid\Core\SyntaxTree\AbstractNode $node Node to push to node stack
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function pushNodeToStack(\F3\Fluid\Core\SyntaxTree\AbstractNode $node) {
		array_push($this->nodeStack, $node);
	}

	/**
	 * Get the top stack element, without removing it.
	 *
	 * @return \F3\Fluid\Core\SyntaxTree\AbstractNode the top stack element.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getNodeFromStack() {
		return $this->nodeStack[count($this->nodeStack)-1];
	}

	/**
	 * Pop the top stack element (=remove it) and return it back.
	 *
	 * @return \F3\Fluid\Core\SyntaxTree\AbstractNode the top stack element, which was removed.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function popNodeFromStack() {
		return array_pop($this->nodeStack);
	}

	/**
	 * Returns a variable container which will be then passed to the postParseFacet.
	 *
	 * @return \F3\Fluid\Core\VariableContainer The variable container or NULL if none has been set yet
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getVariableContainer() {
		return $this->variableContainer;
	}
}
?>
