<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::ViewHelpers;

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
 * @subpackage ViewHelpers
 * @version $Id:$
 */
/**
 * "If" View Helper.
 * Usage:
 * 
 * Example 1:
 * <f3:if condition="somecondition">
 * This is being shown in case the condition matches
 * </f3:if>
 * 
 * 
 *
 * @package Beer3
 * @subpackage ViewHelpers
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class IfViewHelper extends F3::Beer3::Core::AbstractViewHelper implements F3::Beer3::Core::Facets::ChildNodeAccessInterface {
	
	/**
	 * An array of
	 * @var F3::Beer3::Core::SyntaxTree::AbstractNode
	 */
	protected $childNodes;
	
	public function setChildNodes(array $childNodes) {
		$this->childNodes = $childNodes;
	}

	/**
	 * Initialize arguments. We require only the argument "condition"
	 */
	public function initializeArguments() {
		$this->registerArgument('condition', 'string', 'View helper condition', TRUE);
	}
	/**
	 * Render the if.
	 *
	 * @return string the rendered string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$out = '';
		
		if ($this->arguments['condition']) {
			foreach ($this->childNodes as $childNode) {
				if ($childNode instanceof F3::Beer3::Core::SyntaxTree::ViewHelperNode
				    && $childNode->getViewHelperClassName() == 'F3::Beer3::ViewHelpers::ThenViewHelper' ) {
					return $childNode->render($this->variableContainer);	
				} else { 
					$out .= $childNode->render($this->variableContainer);
				}
			}
		} else {
			foreach ($this->childNodes as $childNode) {
				if ($childNode instanceof F3::Beer3::Core::SyntaxTree::ViewHelperNode
				    && $childNode->getViewHelperClassName() == 'F3::Beer3::ViewHelpers::ElseViewHelper' ) {
					return $childNode->render($this->variableContainer);	
				}
			}
		}
		return $out;
	}
}

?>