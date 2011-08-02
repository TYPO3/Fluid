<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

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
 * Array Syntax Tree Node. Handles JSON-like arrays.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class ArrayNode extends \TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode {

	/**
	 * An associative array. Each key is a string. Each value is either a literal, or an AbstractNode.
	 * @var array
	 */
	protected $internalArray = array();

	/**
	 * Constructor.
	 *
	 * @param array $internalArray Array to store
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($internalArray) {
		$this->internalArray = $internalArray;
	}

	/**
	 * Evaluate the array and return an evaluated array
	 *
	 * @param \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return array An associative array with literal values
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function evaluate(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		$arrayToBuild = array();
		foreach ($this->internalArray as $key => $value) {
			if ($value instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode) {
				$arrayToBuild[$key] = $value->evaluate($renderingContext);
			} else {
				// TODO - this case should not happen!
				$arrayToBuild[$key] = $value;
			}
		}
		return $arrayToBuild;
	}

	/**
	 * INTERNAL; DO NOT CALL DIRECTLY!
	 *
	 * @return array
	 */
	public function getInternalArray() {
		return $this->internalArray;
	}
}

?>