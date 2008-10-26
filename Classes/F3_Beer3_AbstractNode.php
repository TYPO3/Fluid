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
 * Abstract node.
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
abstract class AbstractNode implements F3::Beer3::NodeInterface {
	/**
	 * List of Child Nodes.
	 * @var array F3::Beer3::AbstractNode
	 */
	protected $childNodes = array();
		
	/**
	 * Render all child nodes and return the rendered result string.
	 *
	 * @return string Rendered representation
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderChildNodes() {
		return (string)$this->evaluateChildNodes();
	}
	
	/**
	 * Evaluate all child nodes and return the evaluated results.
	 * 
	 * @return object Normally, an object is returned - in case it is concatenated with a string, a string is returned.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateChildNodes() {
		$output = NULL;
		foreach ($this->childNodes as $subNode) {
			if ($output === NULL) {
				$output = $subNode->evaluate($this->context);
			} else {
				$output = (string)$output;
				$output .= $subNode->render($this->context);
			}
		}
		return $output;
	}
	
	/**
	 * Add an object identified by $key to context
	 *
	 * @param string $key Object identifier
	 * @param object $value Object itself
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addToContext($key, $value) {
		$this->context->add($key, $value);
	}
	
	/**
	 * Removes an object identified by $key from context
	 *
	 * @param string $key Object identifier to remove
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function removeFromContext($key) {
		$this->context->remove($key);
	}
	
	/**
	 * Appends a subnode to this node.
	 * 
	 * @param F3::Beer3::AbstractNode $subnode The subnode to add
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addChildNode(F3::Beer3::AbstractNode $subnode) {
		$this->childNodes[] = $subnode;
	}
	
	/**
	 * Renders the node.
	 * 
	 * @param F3::Beer3::Context context to be used for the rendering
	 * @return string Rendered node as string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render(F3::Beer3::Context $context) {
		return (string)$this->evaluate($context);
	}
	
	/**
	 * Evaluates the node.
	 * @param F3::Beer3::Context context to be used for the evaluation
	 * @return object Evaluated node
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	abstract public function evaluate(F3::Beer3::Context $context);
}


?>