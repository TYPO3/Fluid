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
 * @package 
 * @subpackage 
 * @version $Id:$
 */
/**
 * Enter description here...
 * @scope prototype
 */
class PostParseFacetViewHelper extends F3::Beer3::Core::AbstractViewHelper implements F3::Beer3::Core::Facets::PostParseInterface {
	public static $wasCalled = FALSE;
	
	public function __construct() {
		
	}
	
	public function postParseEvent(F3::Beer3::Core::SyntaxTree::ViewHelperNode $viewHelperNode, $arguments, F3::Beer3::Core::VariableContainer $variableContainer) {
		self::$wasCalled = TRUE;
	}
	public function initializeArguments() {
		
	}
	
	public function render() {
		
	}
}


?>