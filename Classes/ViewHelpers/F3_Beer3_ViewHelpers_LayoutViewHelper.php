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
 * With this tag, you can select a layout to be used.
 *
 * @package Beer3
 * @subpackage ViewHelpers
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 * @todo refine documentation
 */
class LayoutViewHelper extends F3::Beer3::Core::AbstractViewHelper implements F3::Beer3::Core::Facets::PostParseInterface {
	/**
	 * Initialize arguments
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of layout to use. If none given, "default" is used.', TRUE);
	}
	
	/**
	 * On the post parse event, add the "layoutName" variable to the variable container so it can be used by the TemplateView.
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function postParseEvent(F3::Beer3::Core::SyntaxTree::ViewHelperNode $syntaxTreeNode, $viewHelperArguments, F3::Beer3::Core::VariableContainer $variableContainer) {
		if ($viewHelperArguments['name']) {
			$layoutName = $viewHelperArguments['name']->evaluate(new F3::Beer3::Core::VariableContainer());
		} else {
			$layoutName = 'default';
		}

		$variableContainer->add('layoutName', $layoutName);
	}
	
	/**
	 * This tag will not be rendered at all.
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
	}
}


?>