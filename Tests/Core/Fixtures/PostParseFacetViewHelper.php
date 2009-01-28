<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid;

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
 * @package 
 * @subpackage 
 * @version $Id:$
 */
/**
 * Enter description here...
 * @scope prototype
 */
class PostParseFacetViewHelper extends \F3\Fluid\Core\AbstractViewHelper implements \F3\Fluid\Core\Facets\PostParseInterface {
	public static $wasCalled = FALSE;
	
	public function __construct() {
		
	}
	
	public function postParseEvent(\F3\Fluid\Core\SyntaxTree\ViewHelperNode $viewHelperNode, $arguments, \F3\Fluid\Core\VariableContainer $variableContainer) {
		self::$wasCalled = TRUE;
	}
	public function initializeArguments() {
		
	}
	
	public function render() {
		
	}
}


?>
