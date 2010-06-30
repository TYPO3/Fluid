<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Fixtures;

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
 * [Enter description here]
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ConstraintSyntaxTreeNode extends \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode {
	public $callProtocol = array();
	
	public function __construct(\F3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer) {
		$this->variableContainer = $variableContainer;
	}
	
	public function evaluateChildNodes(\F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		$identifiers = $this->variableContainer->getAllIdentifiers();
		$callElement = array();
		foreach ($identifiers as $identifier) {
			$callElement[$identifier] = $this->variableContainer->get($identifier);
		}
		$this->callProtocol[] = $callElement;
	}
	
	public function evaluate(\F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {}
}


?>
