<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 */

/**
 * Abstract node in the syntax tree which has been built.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 * @internal
 */
abstract class AbstractNode {

	/**
	 * List of Child Nodes.
	 * @var array \F3\Fluid\Core\Parser\SyntaxTree\AbstractNode
	 */
	protected $childNodes = array();

	/**
	 * The rendering context containing everything to correctly render the subtree
	 * @var \F3\Fluid\Core\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @param \F3\Fluid\Core\RenderingContext Rendering Context to be used for this evaluation
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function setRenderingContext(\F3\Fluid\Core\RenderingContext $renderingContext) {
		$this->renderingContext = $renderingContext;
	}

	/**
	 * Evaluate all child nodes and return the evaluated results.
	 *
	 * @return object Normally, an object is returned - in case it is concatenated with a string, a string is returned.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function evaluateChildNodes() {
		$output = NULL;
		foreach ($this->childNodes as $subNode) {
			$subNode->setRenderingContext($this->renderingContext);

			if ($output === NULL) {
				$output = $subNode->evaluate();
			} else {
				$output = (string)$output;
				$output .= (string)$subNode->evaluate();
			}
		}
		return $output;
	}

	/**
	 * Appends a subnode to this node. Is used inside the parser to append children
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\AbstractNode $subnode The subnode to add
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function addChildNode(\F3\Fluid\Core\Parser\SyntaxTree\AbstractNode $subNode) {
		$this->childNodes[] = $subNode;
	}

	/**
	 * Evaluates the node - can return not only strings, but arbitary objects.
	 *
	 * @return object Evaluated node
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	abstract public function evaluate();
}

?>