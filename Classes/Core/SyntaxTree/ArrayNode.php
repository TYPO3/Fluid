<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\SyntaxTree;

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
 * Array Syntax Tree Node. Handles JSON-like arrays.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ArrayNode extends \F3\Fluid\Core\SyntaxTree\AbstractNode {
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
	 * @param \F3\Fluid\VariableContainer $variableContainer Variable Container for the scope variables
	 * @return array An associative array with literal values
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluate(\F3\Fluid\Core\VariableContainer $variableContainer) {
		$arrayToBuild = array();
		foreach ($this->internalArray as $key => $value) {
			if ($value instanceof \F3\Fluid\Core\SyntaxTree\AbstractNode) {
				$arrayToBuild[$key] = $value->evaluate($variableContainer);
			} else {
				$arrayToBuild[$key] = $value;
			}
		}
		return $arrayToBuild;
	}
}


?>
