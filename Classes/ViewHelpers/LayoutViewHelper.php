<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 * With this tag, you can select a layout to be used.
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 * @todo refine documentation
 */
class LayoutViewHelper extends \F3\Fluid\Core\AbstractViewHelper implements \F3\Fluid\Core\Facets\PostParseInterface {

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
	 * @param \F3\Fluid\Core\SyntaxTree\ViewHelperNode $syntaxTreeNode
	 * @param array $viewHelperArguments
	 * @param \F3\Fluid\Core\VariableContainer $variableContainer
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	static public function postParseEvent(\F3\Fluid\Core\SyntaxTree\ViewHelperNode $syntaxTreeNode, array $viewHelperArguments, \F3\Fluid\Core\VariableContainer $variableContainer) {
		if ($viewHelperArguments['name']) {
			$viewHelperArguments['name']->setVariableContainer(new \F3\Fluid\Core\VariableContainer());
			$layoutName = $viewHelperArguments['name']->evaluate();
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
