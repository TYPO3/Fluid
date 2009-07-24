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
 * Abstract node in the syntax tree which has been built.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 * @internal
 */
abstract class AbstractNode {

	/**
	 * List of Child Nodes.
	 * @var array<\F3\Fluid\Core\Parser\SyntaxTree\AbstractNode>
	 */
	protected $childNodes = array();

	/**
	 * The rendering context containing everything to correctly render the subtree
	 * @var \F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @param \F3\Fluid\Core\Rendering\RenderingContext Rendering Context to be used for this evaluation
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setRenderingContext(\F3\Fluid\Core\Rendering\RenderingContext $renderingContext) {
		$this->renderingContext = $renderingContext;
	}

	/**
	 * Evaluate all child nodes and return the evaluated results.
	 *
	 * @return object Normally, an object is returned - in case it is concatenated with a string, a string is returned.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function evaluateChildNodes() {
		$output = NULL;
		foreach ($this->childNodes as $subNode) {
			$subNode->setRenderingContext($this->renderingContext);

			if ($output === NULL) {
				$output = $subNode->evaluate();
			} else {
				if (is_object($output) && !method_exists($output, '__toString')) {
					throw new \F3\Fluid\Core\Parser\Exception('Cannot cast object of type "' . get_class($output) . '" to string.', 1248356140);
				}
				$output = (string)$output;
				$subNodeOutput = $subNode->evaluate();
				if (is_object($subNodeOutput) && !method_exists($subNodeOutput, '__toString')) {
					throw new \F3\Fluid\Core\Parser\Exception('Cannot cast object of type "' . get_class($subNodeOutput) . '" to string.', 1248356140);
				}
				$output .= (string)$subNodeOutput;
			}
		}
		return $output;
	}

	/**
	 * Returns all child nodes for a given node.
	 * This is especially needed to implement the boolean expression language.
	 *
	 * @return array F3\Fluid\Core\Parser\SyntaxTree\AbstractNode A list of nodes
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getChildNodes() {
		return $this->childNodes;
	}

	/**
	 * Appends a subnode to this node. Is used inside the parser to append children
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\AbstractNode $subnode The subnode to add
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
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
	 */
	abstract public function evaluate();
}

?>