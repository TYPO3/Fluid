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
 * Stores all information relevant for one parsing pass - that is, the root node,
 * and the current stack of open nodes (nodeStack).
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ParsingState {

	/**
	 * Root node reference
	 * @var F3::Beer3::RootNode
	 */
	protected $rootNode;
	
	/**
	 * Array of node references currently open.
	 * @var array
	 */
	protected $nodeStack = array();
	
	/**
	 * Set root node of this parsing state
	 *
	 * @param F3::Beer3::RootNode $rootNode
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setRootNode(F3::Beer3::RootNode $rootNode) {
		if (!($rootNode instanceof F3::Beer3::RootNode)) throw new F3::Beer3::Exception('Root node must be of type RootNode.', 1224495647);
		$this->rootNode = $rootNode;
	}
	
	/**
	 * Get root node of this parsing state.
	 *
	 * @return F3::Beer3::RootNode The root node
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getRootNode() {
		return $this->rootNode;
	}
	
	/**
	 * Push a node to the node stack.
	 *
	 * @param F3::Beer3::AbstractNode $node Node to push to node stack
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function pushNodeToStack(F3::Beer3::AbstractNode $node) {
		array_push($this->nodeStack, $node);
	}
	
	/**
	 * Get the top stack element, without removing it.
	 * 
	 * @return F3::Beer3::AbstractNode the top stack element.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getNodeFromStack() {
		return $this->nodeStack[count($this->nodeStack)-1];
	}
	
	/**
	 * Pop the top stack element (=remove it) and return it back.
	 *
	 * @return F3::Beer3::AbstractNode the top stack element, which was removed.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function popNodeFromStack() {
		 return array_pop($this->nodeStack);
	}
}
?>